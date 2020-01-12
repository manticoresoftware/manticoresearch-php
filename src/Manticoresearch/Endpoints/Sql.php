<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;


/**
 * Class Sql
 * @package Manticoresearch\Endpoints
 */
class Sql extends Request
{
    /**
     * @return mixed|string
     */
    public function getPath()
    {
        return '/sql';
    }

    /**
     * @return mixed|string
     */
    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return mixed|string
     */
    public function getBody()
    {
        return http_build_query($this->_body);
    }
}