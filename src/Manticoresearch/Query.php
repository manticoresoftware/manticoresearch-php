<?php


namespace Manticoresearch;


class Query implements Arrayable
{
    protected $_params;

    public function add($k,$v) {
        $this->_params[$k] = $v;
    }
    public function toArray()
    {

       return  $this->_toArray($this->_params);
    }

    protected function _toArray($params)
    {
        $return = [];
        foreach ($params as $k => $v) {
            if ($v instanceof Arrayable) {
                $return[$k] = $v->toArray();
            } elseif (is_array($v)) {
                $return[$k] = $this->_toArray($v);
            } else {
                if($v!==null) {
                    $return[$k] = $v;
                }else {
                    return null;
                }

            }
        }
        return $return;
    }
}
