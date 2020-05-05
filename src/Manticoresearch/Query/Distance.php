<?php


namespace Manticoresearch\Query;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Query;

class Distance extends Query
{
    /**
     * Distance constructor.
     * @param array $args
     */
    public function __construct($args = [])
    {
        $this->_params['geo_distance'] = [];
        $this->_params['geo_distance']['distance_type'] = $args['type'] ?? 'adaptive';
        if (count($args) > 0) {
            if (!isset($args['location_anchor'])) {
                throw new RuntimeException('anchors not provided');
            }
            $this->_params['geo_distance']['location_anchor'] = $args['location_anchor'];
            if (!isset($args['location_source'])) {
                throw new RuntimeException('source attributes not provided');
            }
            if (is_array($args['location_source'])) {
                $args['location_source'] = implode(',', $args['location_source']);
            }
            $this->_params['geo_distance']['location_source'] = $args['location_source'];

            if (!isset($args['location_distance'])) {
                throw new RuntimeException('distance not provided');
            }
            $this->_params['geo_distance']['distance'] = $args['location_distance'];
        }
    }

    /**
     * @param string $distance the distance and it's units, e.g. 1000m, 200km
     */
    public function setDistance($distance)
    {
        $this->_params['geo_distance']['distance'] = $distance;
    }

    /**
     * @param array|string  $source Either an array or comma separated string of the fields to reference for lat & lon
     */
    public function setSource($source)
    {
        if (is_array($source)) {
            $source = implode(',', $source);
        }
        $this->_params['geo_distance']['location_source'] = $source;
    }

    /**
     * Set the location of the anchor, namely the point by which distances will be measured from
     *
     * @param float $lat the latitude of the anchor
     * @param float $lon the longitude of the anchor
     */
    public function setAnchor($lat, $lon)
    {
        $this->_params['geo_distance']['location_anchor'] = ['lat' => $lat, 'lon' => $lon];
    }

    /**
     * @param string $algorithm the algorithm for distance measurement, either adaptive or haversine
     */
    public function setDistanceType($algorithm)
    {
        $this->_params['geo_distance']['distance_type'] = $algorithm ?? 'adaptive';
    }
}
