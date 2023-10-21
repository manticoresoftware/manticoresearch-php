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
    protected $cluster;

    public function setBody($params = null)
    {
        if (isset($this->cluster)) {
            return parent::setBody(['query' => "DELETE CLUSTER " . $this->cluster]);
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
