<?php

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

class Bulk extends Request
{
    public function getPath()
    {
        return '/json/bulk';
    }

    public function getMethod()
    {
        return 'POST';
    }

    public function getContentType()
    {
        return 'application/x-ndjson';
    }

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