<?php


namespace Manticoresearch;


class Response
{
    protected $_time;
    protected $_string;
    protected $_response;

    public function __construct($responseString,$status=null)
    {
        if(is_array($responseString)) {
            $this->_response = $responseString;
        }else {
            $this->_string = $responseString;
        }
        $this->_status = $status;
    }

    public function getResponse()
    {
        if(null == $this->_response) {
            try {
                $this->_response = json_decode($this->_string,true);
            } catch(\JsonException $e) {

            }
            if(empty($this->_response)) {
                $this->_response = [];
            }
        }
        return $this->_response;
    }
    public function hasError()
    {
        $response = $this->getResponse();
        return isset($response['error']);
    }
    public function getError()
    {
        $response = $this->getResponse();
        if(isset($response['error'])) {
            return json_encode($response['error'],true);
        }else {
            return '';
        }
    }
    public function setTime($time) {
        $this->_time = $time;
        return $this;
    }
    public function getTime()
    {
        return $this->_time;
    }
}