<?php


namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Set extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_cluster;

    public function setBody($params = null)
    {
        if (isset($params['variable'])) {
            return parent::setBody([
                'query' => "SET CLUSTER" . $this->_cluster . " GLOBAL '" . $params['variable']['name'], "'=" .
                (is_numeric($params['variable']['value']) ? $params['variable']['value'] : "'" . $params['variable']['value'] . "'")
            ]);

        }
        throw new RuntimeException('Variable is missing for /cluster/set');
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