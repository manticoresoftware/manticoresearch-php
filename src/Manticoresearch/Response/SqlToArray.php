<?php

namespace Manticoresearch\Response;

use Manticoresearch\Response;

class SqlToArray extends Response
{
    public function getResponse()
    {
        $response = parent::getResponse();
        if (isset($response['columns'], $response['data'])) {
            $data = [];
            array_walk($response['columns'], static function (&$value, $key) {
                $value = array_keys($value)[0];
            });
            $id = -1;
            foreach ($response['data'] as $property) {
                if (isset($property['id'])) {
                    $id = $property['id'];
                    unset($property['id']);
                } else $id++;
                $data[$id] = (count($property) == 1) ? array_shift($property):$property;
            }
            if (count($data) > 0) {
                return $data;
            }
        }
        return $response;
    }
}
