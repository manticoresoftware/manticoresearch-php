<?php


namespace Manticoresearch;

use http\Exception\RuntimeException;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Distance;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\Match;
use Manticoresearch\Query\QueryString;
use Manticoresearch\Query\Range;
use Manticoresearch\Query\ScriptFields;

/**
 * Manticore search object
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 */
class Search
{
    /**
     * @var Client
     */
    protected $_client;

    protected $_query;
    protected $_body;
    /**
     * @var array
     */
    protected $_params = [];

    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->_query = new BoolQuery();
    }

    public function setIndex($index): self
    {
        $this->_params['index'] = $index;
        return $this;
    }

    public function setSource($source): self
    {
        $this->_params['_source'] = $source;
        return $this;
    }

    /**
     * @param $string
     * @return $this
     */
    public function search($string): self
    {
        if (is_object($string)) {
            $this->_query = $string;
            return $this;
        }
        $this->_query->must(new QueryString($string));
        return $this;
    }
    public function match($keywords, $fields=null):self
    {
        $f = "*";
        if ($fields !== null && is_string($fields)) {
            $f = $fields;
        }
        $this->_query->must(new Match($keywords, $f));
        return $this;
    }
    public function phrase($string, $fields=null):self
    {
        $f = "*";
        if ($fields !== null && is_string($fields)) {
            $f = $fields;
        }
        $this->_query->must(new Match($string, $f));
        return $this;
    }
    public function limit($limit): self
    {
        $this->_params['limit'] = $limit;
        return $this;
    }

    /**
     * @param $name
     * @param $exp
     * @return $this
     */
    public function expression($name, $exp): self
    {
        if (!isset($this->_params['script_fields'])) {
            $this->_params['script_fields'] = new ScriptFields();
        }
        $this->_params['script_fields']->add($name, $exp);
        return $this;
    }

    public function highlight($fields = []): self
    {
        if (count($fields) > 0) {
            $hfields = new Query();
            foreach ($fields as $f) {
                $hfields->add($f, null);
            }
            $this->_params['highlight'] = $hfields->toArray();
        } else {
            $this->_params['highlight'] = new \stdClass();
        }

        return $this;
    }

    public function distance($args): self
    {
        $this->_query->must(new Distance($args));
        return $this;
    }

    public function filter($attr, $op = '', $values = []): self
    {
        if (is_object($attr)) {
            $this->_query->must($attr);
            return $this;
        }
        if (!is_array($values)) {
            $values = [$values];
        }

        switch ($op) {
            case 'range':
                $this->_query->must(new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]));
                break;
            case 'lt':
            case 'lte':
            case 'gt':
            case 'gte':
                $this->_query->must(new Range($attr, [
                    $op => $values[0],
                ]));
                break;
            case 'equals':
                $this->_query->must(new Equals($attr, $values[0]));
                break;
        }
        return $this;
    }

    public function orFilter($attr, $op = '', $values = []): self
    {
        if (is_object($attr)) {
            $this->_query->should($attr);
            return $this;
        }
        if (!is_array($values)) {
            $values = [$values];
        }
        switch ($op) {
            case 'range':
                $this->_query->should(new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]));
                break;
            case 'lt':
            case 'lte':
            case 'gte':
            case 'gt':
                $this->_query->should(new Range($attr, [
                    $op => $values[0],
                ]));
                break;
            case 'equals':
                $this->_query->should(new Equals($attr, $values[0]));
                break;
        }
        return $this;
    }

    public function notFilter($attr, $op = '', $values = []): self
    {
        if (is_object($attr)) {
            $this->_query->mustNot($attr);
            return $this;
        }
        if (!is_array($values)) {
            $values = [$values];
        }

        switch ($op) {
            case 'range':
                $this->_query->mustNot(new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]));
                break;
            case 'lt':
            case 'lte':
            case 'gte':
            case 'gt':
                $this->_query->mustNot(new Range($attr, [
                    $op => $values[0],
                ]));
                break;
            case 'equals':
                $this->_query->mustNot(new Equals($attr, $values[0]));
                break;
        }
        return $this;
    }

    public function offset($offset): self
    {
        $this->_params['offset'] = $offset;
        return $this;
    }

    public function maxMatches($maxmatches): self
    {
        $this->_params['max_matches'] = $maxmatches;
        return $this;
    }

    public function sort($field, $direction = 'asc', $mode = null): self
    {
        // reset sorting
        if ($field === false) {
            $this->_params['sort'] = [];
        }
        //if 1st arg is array means we have a sorting expression
        if (is_array($field)) {
            //is 2nd arg is true we full set the sort with the expr, otherwise just add it
            if (isset($direction) && $direction === true) {
                $this->_params['sort'] = $field;
            } else {
                $this->_params['sort'] [] = $field;
            }
            return $this;
        }
        if (!isset($this->_params['sort'])) {
            $this->_params['sort'] = [];
        }
        if ($mode === null) {
            $this->_params['sort'] [] = [$field => $direction];
        } else {
            $this->_params['sort'] [] = [$field => ['order' => $direction, 'mode' => $mode]];
        }

        return $this;
    }

    public function profile(): self
    {
        $this->_params['profile'] = true;
        return $this;
    }

    public function get()
    {
        $this->_body = $this->compile();
        $resp = $this->_client->search(['body' => $this->_body],true);
        return new ResultSet($resp);

    }

    public function compile()
    {
        $body = $this->_params;
        $query = $this->_query->toArray();
        if ($query !== null) {
            $body['query'] = $query;
        }

        if (isset($this->_params['script_fields'])) {
            $body['script_fields'] = $this->_params['script_fields']->toArray();
            unset($this->_params['script_fields']);
        }

        return $body;
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function reset()
    {
        $this->_params = [];
        $this->_query = new BoolQuery();
    }
    public function getClient()
    {
        return $this->_client;
    }
}
