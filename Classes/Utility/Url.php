<?php namespace CIC\Cicbase\Utility;

/**
 * Class Url
 * @package CIC\Cicbase\Utility
 */
class Url {
    /**
     * @param string $url
     * @param string|array $paramName
     * @return string
     */
    public static function stripQueryStringParameters($url, $paramName) {
        $paramNames = is_string($paramName) ? [$paramName] : $paramName;
        $queryString = '';

        $params = static::urlToParamsArray($url);

        if(count($params)) {
            $temp = [];
            foreach ($params as $name => $val) {
                if (in_array($name, $paramNames)) {
                    /**
                     * Skip it
                     */
                    continue;
                }

                $temp[$name] = $val;
            }
            if (count($temp)) {
                $queryString = '?' . http_build_query($temp);
            }
        }
        return static::stripAllQueryStringParameters($url) . $queryString;
    }

    /**
     * @param $url
     * @return string
     */
    protected static function stripAllQueryStringParameters($url) {
        $pos = strpos($url, '?');
        return $pos ? substr($url, 0, $pos) : $url;
    }

    /**
     * @param $url
     * @param $addParams
     * @return mixed
     */
    public static function addQueryStringParameters($url, $addParams) {
        $newParams = array_merge(static::urlToParamsArray($url), $addParams);
        $queryString = '';
        if (count($newParams)) {
            $queryString = '?' . http_build_query($newParams);
        }
        return static::stripAllQueryStringParameters($url) . $queryString;
    }

    /**
     * @return array
     */
    protected static function urlToParamsArray($url) {
        $out = [];
        if (!$url) {
            return $out;
        }
        $parsed = parse_url($url);
        if ($parsed['query']) {
            parse_str($parsed['query'], $params);
            $out = $params;
        }

        return $out;
    }
}
