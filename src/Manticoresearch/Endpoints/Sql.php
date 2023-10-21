<?php

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Sql
 * @package Manticoresearch\Endpoints
 */
class Sql extends Request
{
    /**
     * @return mixed|string
     */
    protected $mode;
    public function getPath()
    {
        return '/sql';
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
    public function getBody()
    {
        if ($this->mode === 'raw') {
            $return = ['mode=raw'];
            foreach ($this->body as $k => $v) {
                $return[] = $k . '=' . urlencode($v);
            }
            return implode('&', $return);
        } else {
            return http_build_query($this->body);
        }
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }
}
