<?php


namespace Manticoresearch\Response;


use Manticoresearch\Response;

class SqlToArray extends Response
{
    public function getResponse()
    {
        $response = parent::getResponse();
        $return =$response;

        if(isset($response['columns']) && isset($response['data']))
        {
            $return['data']=[];
            $names = array_walk($response['columns'],function(&$value,$key) {$value= array_keys($value)[0];});
            foreach($response['data'] as $property) {
                if(count($response['columns'])>2) {
                    $return['data'] [] = $property;
                }else{
                    $return['data'][$property[$response['columns'][0]]] = $property[$response['columns'][1]];
                }
            }
        }
        unset($return['columns']);
        return $return;
    }
}