<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Utils;

class Suggest extends EmulateBySql
{
    use Utils;
    protected $_index;
    public function setBody($parameters = null)
    {
        $params = [ "'" . Utils::escape($parameters['query']) . "'","'" . $this->_index. "'"];
        if (count($parameters) > 2) {
            foreach (array_splice($parameters, 2) as $name => $value) {
                $params[] = "$value AS $name";
            }
        }
        parent::setBody(['query' => "CALL SUGGEST(" . implode(",", $params) . ")"]);
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