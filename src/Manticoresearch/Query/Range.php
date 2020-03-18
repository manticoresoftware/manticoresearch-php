<?php


namespace Manticoresearch\Query;


use Manticoresearch\Query;

class Range extends Query
{
    public function __construct($field,$args=[])
    {
        $this->_params['range'] = [$field => $args];
    }

}