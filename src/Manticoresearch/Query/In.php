<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class In extends Query
{
    public function __construct($field, $args)
    {
        $this->params['in'] = [$field => $args];
    }
}
