<?php

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Search
 * @package Manticoresearch\Endpoints
 */
class Search extends Request
{
    /**
     * @return mixed|string
     */
    public function getPath()
    {
        return '/json/search';
    }

    /**
     * @return mixed|string
     */
    public function getMethod()
    {
        return 'POST';
    }
}
