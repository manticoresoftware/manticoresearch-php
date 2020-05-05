<?php


namespace Manticoresearch;

use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Distance;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\Match;
use Manticoresearch\Query\MatchPhrase;
use Manticoresearch\Query\QueryString;
use Manticoresearch\Query\Range;
use Manticoresearch\Query\ScriptFields;

/**
 * Manticore search object
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
class Search
{
    /**
     * @var Client
     */
    protected $client;

    protected $query;
    protected $body;
    /**
     * @var array
     */
    protected $params = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->query = new BoolQuery();
    }

    public function setIndex($index): self
    {
        $this->params['index'] = $index;
        return $this;
    }

    public function setSource($source): self
    {
        $this->params['_source'] = $source;
        return $this;
    }

    /**
     * @param string $queryString
     * @return $this
     */
    public function search($queryString): self
    {
        if (is_object($queryString)) {
            $this->query = $queryString;
            return $this;
        }
        $this->query->must(new QueryString($queryString));
        return $this;
    }

    public function match($keywords, $fields = null): self
    {
        $f = "*";
        if ($fields !== null && is_string($fields)) {
            $f = $fields;
        }
        $this->query->must(new Match($keywords, $f));
        return $this;
    }

    public function phrase($string, $fields = null): self
    {
        $f = "*";
        if ($fields !== null && is_string($fields)) {
            $f = $fields;
        }
        $this->query->must(new MatchPhrase($string, $f));
        return $this;
    }

    public function limit($limit): self
    {
        $this->params['limit'] = $limit;
        return $this;
    }

    /**
     * @param string $name
     * @param string $exp
     * @return $this
     */
    public function expression($name, $exp): self
    {
        if (!isset($this->params['script_fields'])) {
            $this->params['script_fields'] = new ScriptFields();
        }
        $this->params['script_fields']->add($name, $exp);
        return $this;
    }

    public function highlight($fields = [], $settings = []): self
    {

        if (count($fields) === 0 && count($settings)===0) {
            $this->params['highlight'] =  new \stdClass();
            return $this;
        }
        $this->params['highlight'] = [];
        if (count($fields) > 0) {
            $this->params['highlight']['fields'] =$fields;
        }
        if (count($settings)>0) {
            foreach ($settings as $name => $value) {
                $this->params['highlight'][$name] =$value;
            }
        }
        return $this;
    }

    public function distance($args): self
    {
        $this->query->must(new Distance($args));
        return $this;
    }

    public function filter($attr, $op = '', $values = []): self
    {
        if (is_object($attr)) {
            $this->query->must($attr);
            return $this;
        }
        if (!is_array($values)) {
            $values = [$values];
        }

        switch ($op) {
            case 'range':
                $this->query->must(new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]));
                break;
            case 'lt':
            case 'lte':
            case 'gt':
            case 'gte':
                $this->query->must(new Range($attr, [
                    $op => $values[0],
                ]));
                break;
            case 'equals':
                $this->query->must(new Equals($attr, $values[0]));
                break;
        }
        return $this;
    }

    public function orFilter($attr, $op = '', $values = []): self
    {
        if (is_object($attr)) {
            $this->query->should($attr);
            return $this;
        }
        if (!is_array($values)) {
            $values = [$values];
        }
        switch ($op) {
            case 'range':
                $this->query->should(new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]));
                break;
            case 'lt':
            case 'lte':
            case 'gte':
            case 'gt':
                $this->query->should(new Range($attr, [
                    $op => $values[0],
                ]));
                break;
            case 'equals':
                $this->query->should(new Equals($attr, $values[0]));
                break;
        }
        return $this;
    }

    public function notFilter($attr, $op = '', $values = []): self
    {
        if (is_object($attr)) {
            $this->query->mustNot($attr);
            return $this;
        }
        if (!is_array($values)) {
            $values = [$values];
        }

        switch ($op) {
            case 'range':
                $this->query->mustNot(new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]));
                break;
            case 'lt':
            case 'lte':
            case 'gte':
            case 'gt':
                $this->query->mustNot(new Range($attr, [
                    $op => $values[0],
                ]));
                break;
            case 'equals':
                $this->query->mustNot(new Equals($attr, $values[0]));
                break;
        }
        return $this;
    }

    public function offset($offset): self
    {
        $this->params['offset'] = $offset;
        return $this;
    }

    public function maxMatches($maxmatches): self
    {
        $this->params['max_matches'] = $maxmatches;
        return $this;
    }

    public function sort($field, $direction = 'asc', $mode = null): self
    {
        // reset sorting
        if ($field === false) {
            $this->params['sort'] = [];
        }
        //if 1st arg is array means we have a sorting expression
        if (is_array($field)) {
            //is 2nd arg is true we full set the sort with the expr, otherwise just add it
            if (isset($direction) && $direction === true) {
                $this->params['sort'] = $field;
            } else {
                $this->params['sort'] [] = $field;
            }
            return $this;
        }
        if (!isset($this->params['sort'])) {
            $this->params['sort'] = [];
        }
        if ($mode === null) {
            $this->params['sort'] [] = [$field => $direction];
        } else {
            $this->params['sort'] [] = [$field => ['order' => $direction, 'mode' => $mode]];
        }

        return $this;
    }

    public function profile(): self
    {
        $this->params['profile'] = true;
        return $this;
    }

    /**
     * @return ResultSet
     */
    public function get()
    {
        $this->body = $this->compile();
        $resp = $this->client->search(['body' => $this->body], true);
        return new ResultSet($resp);
    }

    public function compile()
    {
        $body = $this->params;
        $query = $this->query->toArray();
        if ($query !== null) {
            $body['query'] = $query;
        }

        if (isset($this->params['script_fields'])) {
            $body['script_fields'] = $this->params['script_fields']->toArray();
            unset($this->params['script_fields']);
        }

        return $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function reset()
    {
        $this->params = [];
        $this->query = new BoolQuery();
    }

    public function getClient()
    {
        return $this->client;
    }
}
