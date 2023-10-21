<?php

namespace Manticoresearch\Query;

use Manticoresearch\Query;

class BoolQuery extends Query
{
    public function must($args): self
    {
        $this->params['must'][] = $args;
        return $this;
    }
    public function mustNot($args): self
    {
        $this->params['must_not'][] = $args;
        return $this;
    }
    public function should($args): self
    {
        $this->params['should'][] = $args;
        return $this;
    }
    public function toArray()
    {
        return $this->convertArray(['bool' => $this->params]);
    }
}
