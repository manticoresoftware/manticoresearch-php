<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Tables extends EmulateBySql
{
    /**
     * @var string
     */
    protected $index;

    public function setBody($params = null)
    {
        return parent::setBody(['query' => "SHOW TABLES " .
            (isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : "")]);
    }
}
