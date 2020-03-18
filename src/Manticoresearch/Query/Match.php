<?php


namespace Manticoresearch\Query;


use Manticoresearch\Query;

class Match extends Query
{
    public function __construct(string $string,string $fields)
    {
        $this->_params['match'] =[$fields => $string];
    }

}