<?php

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Utils;

class FlushLogs extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $index;

    public function setBody($params = null)
    {
        return parent::setBody(['query' => "FLUSH LOGS"]);
    }
}
