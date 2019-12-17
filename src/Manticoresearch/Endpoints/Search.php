<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Request;

class Search extends Request
{
    public function getPath()
    {
        return '/json/search';
    }
    public function getMethod()
    {
        return 'POST';
    }
}