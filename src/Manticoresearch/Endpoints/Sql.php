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
    protected $_mode;
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
        if($this->_mode=='raw') {
            $return = ['mode=raw'];
            foreach($this->_body as $k=>$v) {
                $return[]= $k.'='.$v;
                return implode('&',$return);
            }
        }else{
            return http_build_query($this->_body);
        }

    }

    public function getMode()
    {
        return $this->_mode;
    }

    public function setMode($mode)
    {
        $this->_mode = $mode;
    }
}