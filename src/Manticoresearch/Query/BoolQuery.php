<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class BoolQuery extends Query
{
    public function must($args):self
    {
        $this->_params['must'][]= $args;
        return $this;
    }
    public function mustNot($args):self
    {
        $this->_params['must_not'][]= $args;
        return $this;
    }
    public function should($args):self
    {
        $this->_params['should'][]= $args;
        return $this;
    }
    public function toArray()
    {
        return parent::_toArray(['bool'=>$this->_params]);
    }
}
