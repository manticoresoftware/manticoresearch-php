<?php


namespace Manticoresearch;

use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Distance;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\In;
use Manticoresearch\Query\MatchQuery;
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

    protected static $replaceOperator = [
        '=' => 'equals',
        '>=' => 'gte',
        '>' => 'gt',
        '<' => 'lt',
        '<=' => 'lte',
    ];

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
        $this->query->must(new MatchQuery($keywords, $f));
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

    protected function getAttrObject($attr, $op, $values)
    {
        $op = static::$replaceOperator[$op] ?? $op;

        switch ($op) {
            case 'range':
                $object = new Range($attr, [
                    'gte' => $values[0],
                    'lte' => $values[1]
                ]);
                break;
            case 'lt':
            case 'lte':
            case 'gt':
            case 'gte':
                $object = new Range($attr, [
                    $op => $values[0],
                ]);
                break;
            case 'in':
                $object = new In($attr, $values);
                break;
            case 'equals':
                $object = new Equals($attr, $values[0]);
                break;
            default:
                $object = null;
        }

        return $object;
    }

    public function filter($attr, $op = null, $values = null, $boolean = "AND"): self
    {
        if (!is_object($attr)) {
            if (is_null($values)) {
                $values = $op;
                $op = "equals";
            }

            if (!is_array($values)) {
                $values = [$values];
            }

            $attr = $this->getAttrObject($attr, $op, $values);

            if (!$attr) {
                return $this;
            }
        }

        if ($boolean === "AND") {
            $this->query->must($attr);
        } else if($boolean === "OR") {
            $this->query->should($attr);
        } else if($boolean === "NOT") {
            $this->query->mustNot($attr);
        }

        return $this;
    }

    public function orFilter($attr, $op = null, $values = null): self
    {
        return $this->filter($attr, $op, $values, 'OR');
    }

    public function notFilter($attr, $op = null, $values = null): self
    {
        return $this->filter($attr, $op, $values, 'NOT');
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

    public function facet($field, $group = null, $limit = null) : self
    {
        // reset facets
        if ($field === false) {
            $this->params['aggs'] = [];
        }
        if ($group === null) {
            $group = $field;
        }
        $terms = ['field'=>$field];
        if ($limit !==null) {
            $terms['size'] = $limit;
        }
        $this->params['aggs'][$group] =['terms' =>$terms];
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
