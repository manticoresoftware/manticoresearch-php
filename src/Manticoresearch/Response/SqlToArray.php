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
            foreach ($response['data'] as $property) {
                if (count($response['columns']) > 2) {
                    $data[array_shift($property)] = $property;
                } elseif (count($response['columns']) === 2) {
                    $data[$property[$response['columns'][0]]] = $property[$response['columns'][1]];
                }
            }
            if (count($data) > 0) {
                return $data;
            }
        }
        return $response;
    }
}
