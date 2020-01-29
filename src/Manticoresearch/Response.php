<?php


namespace Manticoresearch;


/**
 * Class Response
 * @package Manticoresearch
 */
class Response
{
    /**
    * execution time to get the response
     * @var integer|float
     */
    protected $_time;

    /**
     * raw response as string
     * @var string
     */
    protected $_string;

    /**
     * information about request
     * @var array
     */
    protected $_transportInfo;


    /**
     * response as array
     * @var array|string
     */
    protected $_response;

    public function __construct(string $responseString, $status = null)
    {
        if (is_array($responseString)) {
            $this->_response = $responseString;
        } else {
            $this->_string = $responseString;
        }
        $this->_status = $status;
    }

    /*
     * Return response
     * @return array|mixed|string
     */
    public function getResponse()
    {
        if (null == $this->_response) {
            try {
                $this->_response = json_decode($this->_string, true);
            } catch (\JsonException $e) {

            }
            if (empty($this->_response)) {
                $this->_response = [];
            }
        }
        return $this->_response;
    }

    /*
     * Check whenever response has error
     * @return bool
     */
    public function hasError()
    {
        $response = $this->getResponse();
        return isset($response['error']);
    }

    /*
     * Return error
     * @return false|string
     */
    public function getError()
    {
        $response = $this->getResponse();
        if (isset($response['error'])) {
            return json_encode($response['error'], true);
        } else {
            return '';
        }
    }

    /*
     * set execution time
     * @param int|float $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    /*
     * returns execution time
     * @return mixed
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     *  set request info
     * @param array
     * @return $this
     */
    public function setTransportInfo($info)
    {
        $this->_transportInfo = $info;
        return $this;
    }

    /**
     * get request info
     * @return array
     */
    public function getTransportInfo()
    {
        return $this->_transportInfo;
    }
}