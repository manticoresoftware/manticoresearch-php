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
                    if (count($property) > 1) unset($property['id']);
                } else if (isset($this->params['customMapping']) and $this->params['customMapping']) {
                    if (isset($property['Field'])) {
                        $id = $property['Field'];
                        if (count($property) > 1) unset($property['Field']);
                    } else if (isset($property['Variable_name'])) {
                        $id = $property['Variable_name'];
                        if (count($property) > 1) unset($property['Variable_name']);
                    } else if (isset($property['Index'])) {
                        $id = $property['Index'];
                        if (count($property) > 1) unset($property['Index']);
                    } else if (isset($property['Counter'])) {
                        $id = $property['Counter'];
                        if (count($property) > 1) unset($property['Counter']);
                    } else if (isset($property['Key'])) {
                        $id = $property['Key'];
                        if (count($property) > 1) unset($property['Key']);
                    } else if (isset($property['command'])) {
                        $id = $property['command'];
                        if (count($property) > 1) unset($property['command']);
                    } else if (isset($property['suggest'])) {
                        $id = $property['suggest'];
                        if (count($property) > 1) unset($property['suggest']);
                    } else if (isset($property['Variable'])) {
                        $id = $property['Variable'];
                        if (count($property) > 1) unset($property['Variable']);
                    } else {
                        $id++;
                    }
                } else {
                    $id++;
                }
                $data[$id] = (count($property) == 1)?array_shift($property):$property;
            }
            if (count($data) > 0) {
                return $data;
            }
        }
        return $response;
    }
}
