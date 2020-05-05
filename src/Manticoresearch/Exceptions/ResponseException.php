<?php


namespace Manticoresearch\Exceptions;

use Manticoresearch\Request;
use Manticoresearch\Response;
use Throwable;

/**
 * Class ResponseException
 * @package Manticoresearch\Exceptions
 */
class ResponseException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     * ResponseException constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        parent::__construct($response->getError());
    }

    /**
     * @return Request
     */
    public function getRequest() :Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse() :Response
    {
        return $this->response;
    }
}
