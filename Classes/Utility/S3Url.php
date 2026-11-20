<?php
namespace CIC\Cicbase\Utility;

/**
 * Builds the public URL of an object stored by FileRepository in S3 or an
 * S3-compatible store such as MinIO.
 *
 * The URL comes from the `AWSPublicUrl` setting, a template with `{bucket}` and
 * `{key}` placeholders. Without it, the URL is AWS's virtual-hosted style,
 * http://{bucket}.s3.amazonaws.com/{key}, which is what cicbase has always
 * produced. Example for a local MinIO:
 *
 *   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cicbase']['document']['awsConfs']['AWSPublicUrl']
 *       = 'https://minio.example.orb.local/{bucket}/{key}';
 *
 * Settings are read the way FileRepository's constructor reads them: the
 * extension configuration, overridden by EXTCONF cicbase.document.awsConfs.
 * TypoScript settings are not consulted, because models and the backend form
 * have no TypoScript context.
 */
class S3Url
{
    const DEFAULT_TEMPLATE = 'http://{bucket}.s3.amazonaws.com/{key}';

    /**
     * @param string $bucket
     * @param string $key Object key, e.g. "path/to/file.pdf"; not URL-encoded
     * @return string
     */
    public static function forObject($bucket, $key)
    {
        $template = static::configuration()['AWSPublicUrl'] ?? '';
        if (!$template) {
            $template = static::DEFAULT_TEMPLATE;
        }

        return strtr($template, [
            '{bucket}' => $bucket,
            '{key}' => ltrim($key, '/'),
        ]);
    }

    /**
     * @return array
     */
    protected static function configuration()
    {
        $configuration = (array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicbase'] ?? '');
        $overrides = Arr::safePath($GLOBALS, 'TYPO3_CONF_VARS.EXTCONF.cicbase.document.awsConfs');
        if ($overrides) {
            $configuration = array_merge($configuration, $overrides);
        }
        return $configuration;
    }
}
