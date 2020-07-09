<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class Percolate extends Query
{
    public function __construct($docs)
    {
        $this->params['percolate'] = [];
        if (isset($docs[0]) && (is_array($docs[0]))) {
            $this->params['percolate'] ['documents'] = $docs;
        } else {
            $this->params['percolate'] ['document'] = $docs;
        }
    }
}
