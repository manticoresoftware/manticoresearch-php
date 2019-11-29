<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;


class Sql extends Request
{
    public function getPath()
    {
        return '/sql';
    }
    public function getMethod()
    {
        return 'POST';
    }

    public function getBody()
    {
        return http_build_query($this->_body);
    }
}