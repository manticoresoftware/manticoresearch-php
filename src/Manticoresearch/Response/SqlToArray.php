<?php


namespace Manticoresearch\Response;

use Manticoresearch\Response;

class SqlToArray extends Response
{
    public function getResponse()
    {
        $response = parent::getResponse();

        if (isset($response['columns'], $response['data'])) {
            $data=[];
           array_walk($response['columns'], static function (&$value, $key) {
                $value= array_keys($value)[0];
            });
            foreach ($response['data'] as $property) {
                if (count($response['columns'])>2) {
                    $data[array_shift($property)] = $property;
                } else {
                    $nCols = count($response['columns']);

                    if ($nCols === 2) {
                        $value = $property[$response['columns'][1]];
                        $data[$property[$response['columns'][0]]] = $value;
                    }
                }
            }
            return $data;
        }
        return $response;
    }
}
