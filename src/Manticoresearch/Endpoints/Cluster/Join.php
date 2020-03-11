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
class Join extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_cluster;

    public function setBody($params = null)
    {
        if (isset($this->_cluster)) {
            if(isset($params['node'])) {
                $this->_body = ['query' => "JOIN CLUSTER ".$this->_cluster." AT ".$params['node']];
            }else{
                $options =[];
                if(isset($params['path'])) {
                    $options[] = "'".$params['path']. "' AS path";
                }
                if(isset($params['nodes'])) {
                    $options[] = "'".$params['nodes']. "' AS nodes";
                }
                $this->_body = ['query' => "JOIN CLUSTER ".$this->_cluster.
                    ((count($options)>0)?" ".implode(',', $options):"")];
            }

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