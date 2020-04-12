<?php


namespace Manticoresearch;


use Manticoresearch\Exceptions\RuntimeException;

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

    protected $_status;
    /**
     * response as array
     * @var array
     */
    protected $_response;

    public function __construct( $responseString, $status = null)
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
     * @return array
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            try {
                $this->_response = json_decode($this->_string, true);
            } catch (\Exception $e) {
                throw new RuntimeException('fatal error while trying to decode JSON response');
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
        return isset($response['error']) && $response['error'] !== '';
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
            return false;
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
     * @param array $info
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