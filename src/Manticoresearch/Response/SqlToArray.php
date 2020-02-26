<?php


namespace Manticoresearch\Response;


use Manticoresearch\Response;

class SqlToArray extends Response
{
    public function getResponse()
    {
        $response = parent::getResponse();

        if(isset($response['columns']) && isset($response['data']))
        {
            $data=[];
            $names = array_walk($response['columns'],function(&$value,$key) {$value= array_keys($value)[0];});
            foreach($response['data'] as $property) {
                if(count($response['columns'])>2) {
                    $data[array_shift($property)] = $property;
                }else{
                    $data[$property[$response['columns'][0]]] = $property[$response['columns'][1]];
                }
            }
            return $data;
        }
        return $response;

    }
}