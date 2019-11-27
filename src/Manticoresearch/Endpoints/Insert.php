<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Request;

class Insert extends Request
{
    public function getPath()
    {
        return '/json/insert';
    }
    public function getMethod()
    {
        return 'POST';
    }
}