<?php

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

class Delete extends Request
{
    public function getPath()
    {
        return '/json/delete';
    }

    public function getMethod()
    {
        return 'POST';
    }
}