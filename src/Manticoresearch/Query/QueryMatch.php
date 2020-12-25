<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class QueryMatch extends Query
{
    public function __construct($keywords, $fields)
    {
        $this->params['match'] =[$fields => $keywords];
    }
}
