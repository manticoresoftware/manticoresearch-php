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
class Delete extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_cluster;

    public function setBody($params)
    {
        if (isset($this->_cluster)) {

            return parent::setBody(['query' => "DELETE CLUSTER ".$this->_cluster]);
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