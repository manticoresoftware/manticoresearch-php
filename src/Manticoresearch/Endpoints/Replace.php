<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Request;

class Replace extends Request
{
    public function getPath()
    {
        return '/json/replace';
    }
    public function getMethod()
    {
        return 'POST';
    }
}