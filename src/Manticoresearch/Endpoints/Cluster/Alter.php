<?php

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Alter extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $cluster;

    public function setBody($params = null)
    {
        if (isset($this->cluster)) {
            if (isset($params['operation'])) {
                switch ($params['operation']) {
                    case 'add':
                        if (isset($params['index'])) {
                            return parent::setBody(['query' => "ALTER CLUSTER " .
                                $this->cluster . " ADD  " . $params['index']]);
                        }
                        throw new RuntimeException('Index name is missing.');
                        break;
                    case 'drop':
                        if (isset($params['index'])) {
                            return parent::setBody(['query' => "ALTER CLUSTER " .
                                $this->cluster . " DROP  " . $params['index']]);
                        }
                        throw new RuntimeException('Index name is missing.');
                        break;
                    case 'update':
                        return parent::setBody(['query' => "ALTER CLUSTER " .$this->cluster . " UPDATE nodes"]);
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
        return $this->cluster;
    }

    /**
     * @param mixed $cluster
     */
    public function setCluster($cluster)
    {
        $this->cluster = $cluster;
    }
}
