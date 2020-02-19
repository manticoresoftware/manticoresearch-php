<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Utils;

class Keywords extends EmulateBySql
{
    use Utils;

    public function setBody($parameters)
    {
        $params = [];
        $params[] = "'" . Utils::escape($parameters['query']) . "'";
        $params[] = "'" . $parameters['index'] . "'";
        if (count($parameters) > 2) {
            foreach (array_splice($parameters, 2) as $name => $value) {
                $params[] = "$value AS $name";
            }
        }
        $this->_body = ['query' => "CALL KEYWORDS(" . implode(",", $params) . ")"];
    }
}