<?php


namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Set extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $cluster;

    public function setBody($params = null)
    {
        if (isset($params['variable'])) {
            return parent::setBody([
                'query' => "SET CLUSTER" . $this->cluster . " GLOBAL '" . $params['variable']['name'], "'=" .
                (is_numeric($params['variable']['value']) ?
                    $params['variable']['value'] : "'" . $params['variable']['value'] . "'")
            ]);
        }
        throw new RuntimeException('Variable is missing for /cluster/set');
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
