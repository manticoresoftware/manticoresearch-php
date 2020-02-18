<?php

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;

class AgentStatus extends Sql
{

    public function setBody($params)
    {
        return parent::setBody(['query' => "SHOW AGENT " . (isset($params['agent']) ? $params['agent'] . "'" : "") . " STATUS " . (isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : "")]);
    }
}