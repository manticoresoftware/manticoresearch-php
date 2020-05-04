<?php

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * @todo maybe pattern should be a query parameter rather than body?
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class Delete extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $_cluster;

    public function setBody($params = null)
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