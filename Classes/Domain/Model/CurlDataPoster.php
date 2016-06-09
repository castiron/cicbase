<?php namespace CIC\Cicbase\Domain\Model;

/**
 * Class CurlPostable
 * @package CIC\Cicbase\Domain\Model
 */
class CurlDataPoster extends AbstractDataPoster {
    /**
     *
     */
    public function post() {
        $mapped = $this->dataToPost();
        return $this->curlPost(
            $mapped,
            $this->endpoint()
        );
    }

    /**
     * @param $data
     * @return string
     */
    protected static function postRequestString($data) {
        $temp = [];
        foreach($data as $k => $v) {
            $temp[] = urlencode($k).'='.urlencode($v);
        }
        return implode('&', $temp);
    }

    /**
     * @param $data
     * @param $endpoint
     * @return mixed
     */
    protected function curlPost($data, $endpoint) {
        $request = static::postRequestString($data);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => count($data),
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_USERAGENT => 'TYPO3 Web Form',
        ]);

        $success = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $success && static::httpCodeToSuccess($code);

    }

    /**
     * @param $code
     * @return bool
     */
    protected static function httpCodeToSuccess($code) {
        if (strpos($code, '5') === 0) {
            return false;
        }
        if (strpos($code, '4') === 0) {
            return false;
        }
        return true;
    }
}
