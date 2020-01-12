<?php

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Bulk
 * @package Manticoresearch\Endpoints
 */
class Bulk extends Request
{
    /**
     * @return mixed|string
     */
    public function getPath()
    {
        return '/json/bulk';
    }

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
    public function getContentType()
    {
        return 'application/x-ndjson';
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        if (is_array($body) || $body instanceof \Traversable) {
            foreach ($body as $b) {
                $this->body .= json_encode($b, true) . "\n";
            }
        } else {
            $this->_body = $body;
        }
    }
}