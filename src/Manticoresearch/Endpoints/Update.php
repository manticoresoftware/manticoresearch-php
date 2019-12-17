<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Request;

class Update extends Request
{
    public function getPath()
    {
        return '/json/update';
    }
    public function getMethod()
    {
        return 'POST';
    }
}