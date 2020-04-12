<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class QueryString extends Query
{
    public function __construct($string)
    {
        $this->_params['query_string'] = $string;
    }
}
