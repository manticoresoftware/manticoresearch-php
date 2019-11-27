<?php


namespace Manticoresearch\Endpoints\Pq;


use Manticoresearch\Request;

class DeleteByQuery extends Request
{

    protected $_index;
    public function getPath()
    {
        if(isset($this->_index)) {
            return "/json/pq/".$this->_index."/_search";

        }
        //@todo throw error, index is mandatory
    }
    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->_index = $index;
    }

}