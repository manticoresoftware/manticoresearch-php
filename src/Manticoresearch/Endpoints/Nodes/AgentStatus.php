<?php

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class AgentStatus extends EmulateBySql
{

    public function setBody($params = null)
    {
        return parent::setBody(['query' => "SHOW AGENT " . (isset($params['agent']) ? $params['agent'] . "'" : "") . " STATUS " . (isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : "")]);
    }
}