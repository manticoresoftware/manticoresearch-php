<?php


namespace Manticoresearch\Exceptions;

use Manticoresearch\Request;
use Throwable;

/**
 * Class ConnectionException
 * @package Manticoresearch\Exceptions
 */
class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var Request
     */
    protected $_request;

    /**
     * ConnectionException constructor.
     * @param string $message
     * @param Request|null $request
     */
    public function __construct($message = '', Request $request=null)
    {
        $this->_request = $request;
        parent::__construct($message);
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
