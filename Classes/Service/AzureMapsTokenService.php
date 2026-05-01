<?php
namespace CIC\Cicbase\Service;

use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Mints short-lived Azure Maps SAS tokens for use in the browser.
 *
 * Calls the Azure Management `listSas` operation against the Maps account,
 * authenticated as a service principal (Microsoft Entra app). SAS tokens are
 * cached in the TYPO3 cache until shortly before expiry so we don't hit
 * Management every page load.
 *
 * Required config (typically from EXTCONF):
 *   tenantId           Microsoft Entra tenant GUID
 *   clientId           Service principal (app registration) client ID
 *   clientSecret       Service principal client secret
 *   subscriptionId     Azure subscription containing the Maps account
 *   resourceGroup      Resource group containing the Maps account
 *   mapsAccountName    Name of the Azure Maps account
 *   principalId        Object ID of the user-assigned managed identity
 *                      attached to the Maps account
 *   regions            (optional) array of Azure regions, e.g. ['eastus', 'westus2']
 *   maxRatePerSecond   (optional) integer rate cap; default 50
 *   ttlSeconds         (optional) SAS lifetime in seconds, max 86400; default 3600
 */
class AzureMapsTokenService {
    use ExtbaseInstantiable;

    const DEFAULT_TTL_SECONDS = 3600;
    const DEFAULT_MAX_RATE_PER_SECOND = 50;
    const TOKEN_CACHE_KEY_PREFIX = 'azure_maps_sas_';
    /** Refresh tokens this many seconds before their actual expiry. */
    const REFRESH_LEAD_SECONDS = 600;

    protected $config = array();

    /** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface */
    protected $cache;

    public function __construct($config = array()) {
        $required = array('tenantId', 'clientId', 'clientSecret', 'subscriptionId', 'resourceGroup', 'mapsAccountName', 'principalId');
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new \RuntimeException("AzureMapsTokenService: missing required config field '$field'.");
            }
        }
        $this->config = $config;
        $this->cache = $this->getCache();
    }

    /**
     * Returns a SAS token that's valid for at least REFRESH_LEAD_SECONDS more.
     * Result shape: ['token' => string, 'expiresAt' => unix-ts].
     */
    public function getToken() {
        $cacheKey = self::TOKEN_CACHE_KEY_PREFIX . md5($this->config['mapsAccountName'] . '|' . $this->config['principalId']);

        if ($this->cache && $this->cache->has($cacheKey)) {
            $cached = unserialize($this->cache->get($cacheKey));
            if (is_array($cached) && !empty($cached['expiresAt']) && $cached['expiresAt'] - self::REFRESH_LEAD_SECONDS > time()) {
                return $cached;
            }
        }

        $minted = $this->mintSasToken();
        if ($this->cache) {
            // Cache lifetime is the token lifetime less the refresh lead, so the
            // cache record drops just before the token would be considered stale.
            $cacheLifetime = max(60, ($minted['expiresAt'] - time()) - self::REFRESH_LEAD_SECONDS);
            $this->cache->set($cacheKey, serialize($minted), array(), $cacheLifetime);
        }
        return $minted;
    }

    /**
     * Calls Azure Management to mint a new SAS token.
     */
    protected function mintSasToken() {
        $managementToken = $this->getManagementAccessToken();

        $ttl = isset($this->config['ttlSeconds']) ? min(86400, max(60, (int)$this->config['ttlSeconds'])) : self::DEFAULT_TTL_SECONDS;
        $maxRate = isset($this->config['maxRatePerSecond']) ? (int)$this->config['maxRatePerSecond'] : self::DEFAULT_MAX_RATE_PER_SECOND;

        $start = gmdate('Y-m-d\TH:i:s\Z', time());
        $expiry = gmdate('Y-m-d\TH:i:s\Z', time() + $ttl);

        $url = sprintf(
            'https://management.azure.com/subscriptions/%s/resourceGroups/%s/providers/Microsoft.Maps/accounts/%s/listSas?api-version=2023-06-01',
            urlencode($this->config['subscriptionId']),
            urlencode($this->config['resourceGroup']),
            urlencode($this->config['mapsAccountName'])
        );

        $body = array(
            'signingKey' => 'primaryKey',
            'principalId' => $this->config['principalId'],
            'maxRatePerSecond' => $maxRate,
            'start' => $start,
            'expiry' => $expiry,
        );
        if (!empty($this->config['regions']) && is_array($this->config['regions'])) {
            $body['regions'] = array_values($this->config['regions']);
        }

        $response = $this->httpJson($url, 'POST', $body, array(
            'Authorization: Bearer ' . $managementToken,
            'Content-Type: application/json',
        ));

        if (empty($response['accountSasToken'])) {
            throw new \RuntimeException('Azure Maps listSas response missing accountSasToken.');
        }

        return array(
            'token' => $response['accountSasToken'],
            'expiresAt' => time() + $ttl,
        );
    }

    /**
     * client_credentials grant against the Microsoft Entra token endpoint.
     * Returns the bearer token string.
     */
    protected function getManagementAccessToken() {
        $url = 'https://login.microsoftonline.com/' . urlencode($this->config['tenantId']) . '/oauth2/v2.0/token';
        $body = http_build_query(array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->config['clientId'],
            'client_secret' => $this->config['clientSecret'],
            'scope' => 'https://management.azure.com/.default',
        ));

        $response = $this->httpForm($url, $body);

        if (empty($response['access_token'])) {
            throw new \RuntimeException('Failed to obtain Azure Management access token.');
        }
        return $response['access_token'];
    }

    protected function httpJson($url, $method, $body, $headers) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $resBody = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($resBody === false) {
            throw new \RuntimeException("Azure Management call failed: $error");
        }
        $decoded = json_decode($resBody, true);
        if ($status >= 400) {
            $msg = is_array($decoded) && isset($decoded['error']['message']) ? $decoded['error']['message'] : $resBody;
            throw new \RuntimeException("Azure Management call returned HTTP $status: $msg");
        }
        return is_array($decoded) ? $decoded : array();
    }

    protected function httpForm($url, $body) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $resBody = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($resBody === false) {
            throw new \RuntimeException("Entra token call failed: $error");
        }
        $decoded = json_decode($resBody, true);
        if ($status >= 400) {
            $msg = is_array($decoded) && isset($decoded['error_description']) ? $decoded['error_description'] : $resBody;
            throw new \RuntimeException("Entra token call returned HTTP $status: $msg");
        }
        return is_array($decoded) ? $decoded : array();
    }

    protected function getCache() {
        try {
            return GeneralUtility::makeInstance(CacheManager::class)->getCache('cicbase_cache');
        } catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
            return null;
        }
    }
}
