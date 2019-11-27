<?php


namespace Manticoresearch\Exceptions;


use Manticoresearch\Request;
use Manticoresearch\Response;
use Throwable;

class ResponseException extends \RuntimeException implements ExceptionInterface
{
    protected $_request;
    protected $_response;

    public function __construct(Request $request,Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;

        parent::__construct($response->getError());
    }

    public function getRequest() :Request
    {
        return $this->_request;
    }
    public function getResponse() :Response
    {
        return $this->_response;
    }

}