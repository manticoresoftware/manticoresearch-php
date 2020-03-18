<?php


namespace Manticoresearch\Query;


use Manticoresearch\Query;

class Equals extends Query
{
    public function __construct($field,$args)
    {
        $this->_params['equals'] = [$field => $args];
    }

}