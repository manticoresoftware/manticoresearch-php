<?php


namespace Manticoresearch\Exceptions;


use Manticoresearch\Request;
use Throwable;

class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    protected $_request;

    public function __construct($message = "", Request $request=null)
    {
        $this->_request = $request;
        parent::__construct($message);
    }

    public function getRequest()
    {
        return $this->_request;
    }
}