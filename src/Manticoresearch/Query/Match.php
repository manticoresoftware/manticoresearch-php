<?php


namespace Manticoresearch\Query;


use Manticoresearch\Query;

class Match extends Query
{
    public function __construct($keywords, $fields)
    {
        $this->_params['match'] =[$fields => $keywords];
    }

}