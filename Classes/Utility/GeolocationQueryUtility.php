<?php namespace CIC\Cicbase\Utility;
use CIC\Cicbase\Domain\Model\LatLng;
use CIC\Cicbase\Traits\Database;
use CIC\Cicbase\Traits\FrontendInstantiating;

/**
 * Class GeolocationUtility
 * @package CIC\Cicbase\Utility
 */
class GeolocationQueryUtility {
    use Database;

    /**
     * @param $latitudeFieldName
     * @param $longitudeFieldName
     * @param LatLng $fromLatLng
     * @param string $unitType Can be 'miles' or 'kilometers'
     * @return string
     */
    public static function distanceQueryField($latitudeFieldName, $longitudeFieldName, LatLng $fromLatLng, $unitType = 'miles') {
        $multiplier = static::earthsRadiusInUnits($unitType);
        return "({$multiplier} * acos(
            cos(radians({$fromLatLng->getLat()}))
            * cos(radians({$latitudeFieldName}))
            * cos(radians({$longitudeFieldName}) - radians({$fromLatLng->getlng()}))
            + sin(radians({$fromLatLng->getLat()})) 
            * sin(radians({$latitudeFieldName}))
        ))";
    }

    /**
     * @param $unitType
     * @return int
     */
    protected static function earthsRadiusInUnits($unitType = 'miles') {
        $radius = array(
            'kilometers' => 6371,
            'miles' => 3959,
        );
        return $radius[$unitType];
    }

    /**
     * @param string $tableName
     * @param string $select
     * @param int|float|string $distance
     * @param LatLng $fromLatLng
     * @param string $addWhere
     * @param string $unitType
     * @param string $groupBy
     * @param string $orderBy
     * @param string $limit
     * @param string $latitudeFieldName
     * @param string $longitudeFieldName
     * @return array
     */
    public static function getRecordsWithin($tableName, $select = '*', $distance, LatLng $fromLatLng, $addWhere = '', $unitType = 'miles', $groupBy = '', $orderBy = '', $limit = '', $latitudeFieldName = 'latitude', $longitudeFieldName = 'longitude') {
        static::initializeFrontend();
        $addSelect = static::distanceQueryField($latitudeFieldName, $longitudeFieldName, $fromLatLng, $unitType) . ' AS calc_distance';
        $having = 'calc_distance < ' . $distance;
        $q = "SELECT $select, " . $addSelect
            . " FROM $tableName "
            . " HAVING " . $having . $addWhere . static::enableFields($tableName)
            . ($groupBy ? " GROUP BY $groupBy" : '')
            . ($orderBy ? " ORDER BY $orderBy" : '')
            . ($limit ? " LIMIT $limit" : '');

        $res = static::db()->sql_query($q);
        $out = [];
        while ($row = static::db()->sql_fetch_assoc($res)) {
            $out[] = $row;
        }
        return $out;
    }
}
