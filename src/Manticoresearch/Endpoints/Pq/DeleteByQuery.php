<?php


namespace Manticoresearch\Endpoints\Pq;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Request;

/**
 * Class DeleteByQuery
 * @package Manticoresearch\Endpoints\Pq
 */
class DeleteByQuery extends Request
{

    /**
     * @var string
     */
    protected $index;

    /**
     * @return mixed|string
     */
    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return mixed|string
     */
    public function getPath()
    {
        if (isset($this->index)) {
            return "/pq/" . $this->index . "/_search";
        }
        throw new RuntimeException('Index name is missing.');
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }
}
