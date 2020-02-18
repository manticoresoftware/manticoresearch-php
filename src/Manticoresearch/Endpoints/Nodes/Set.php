<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;

class Set extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if (isset($params['variable']) && is_array($params['variable'])) {
            return parent::setBody([
                'query' => "SET " . (isset($params['type']) ?  $params['type'] . "'" : "")." ".
                    $params['variable']['name']." '" . $params['variable']['value']
            ]);

        }
        throw new RuntimeException('Variable is missing for /nodes/set');
    }
}
