<?php

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Alter extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_cluster;

    public function setBody($params = null)
    {
        if (isset($this->_index)) {
            if (isset($params['operation'])) {
                switch ($params['operation']) {
                    case 'add':
                        if (isset($params['index'])) {
                            return parent::setBody(['query' => "ALTER CLUSTER " . $this->_cluster . " ADD  " . $params['index']]);
                        }
                        throw new RuntimeException('Index name is missing.');
                        break;
                    case 'drop':
                        if (isset($params['index'])) {
                            return parent::setBody(['query' => "ALTER CLUSTER " . $this->_cluster . " DROP  " . $params['index']]);
                        }
                        throw new RuntimeException('Index name is missing.');
                        break;
                    case 'update':
                        return parent::setBody(['query' => "ALTER CLUSTER " . $this->_cluster . " UPDATE nodes"]);
                        break;
                }
                throw new RuntimeException('Unknown cluster operation');
            }
            throw new RuntimeException('Cluster operation is missing');
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
