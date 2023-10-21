<?php

namespace Manticoresearch\Query;

use Manticoresearch\Query;

class MatchQuery extends Query
{
    public function __construct($keywords, $fields)
    {
        $this->params['match'] = [$fields => $keywords];
    }
}
