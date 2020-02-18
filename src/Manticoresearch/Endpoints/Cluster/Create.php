<?php

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * @todo maybe pattern should be a query parameter rather than body?
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class Create extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_cluster;

    public function setBody($params)
    {
        if (isset($this->_cluster)) {
            $options = [];
            if (isset($params['path'])) {
                $options[] = "'" . $params['path'] . "' AS path";
            }
            if (iseet($params['nodes'])) {
                $options[] = "'" . $params['nodes'] . "' AS nodes";
            }
            return parent::setBody(['query' => "CREATE CLUSTER " . $this->_cluster .
            (count($options) > 0) ? " " . implode(',', $options) : ""]);
        }
        throw new RuntimeException('Cluster name is missing.');
    }

    /**
     * @return mixed
     */
    public function getCLuster()
    {
        return $this->_cluster;
    }

    /**
     * @param mixed $cluster
     */
    public function setCluster($cluster)
    {
        $this->_cluster = $cluster;
    }

}