<?php
/**
 * UNUSED
 */

namespace Manticoresearch\Endpoints;


abstract class AbstractEndpoint
{
    protected $params = [];
    protected $index = null;
    protected $id = null;
    protected $method = null;
    protected $body = null;
    protected $options = [];

    abstract public function getAllowedParams();

    abstract public function getURL();

    abstract public function getMethod();

    public function setIndex($index)
    {
        if (is_array($index)) {
            $index = implode(',', array_map('trim', $index));
        }
        $this->index = urlencode($index);
        return $this;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function validateParams()
    {
        $allowed = $this->getAllowedParams();
        $invalid = array_diff(array_keys($this->params), $allowed);
        if (count($invalid) > 0) {
            //throw some error
        }
    }

    /**
     * @return null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param null $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

}