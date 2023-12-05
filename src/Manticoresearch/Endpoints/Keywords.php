<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Keywords extends EmulateBySql
{
    use Utils;
    protected $index;

    public function setBody($params = null)
    {
        if (isset($this->index)) {
            $binds =[];
            $binds[] = "'" . static::escape($params['query']) . "'";
            $binds[] = "'" . $this->index . "'";
            if (count($params['options']) > 0) {
                foreach ($params['options'] as $name => $value) {
                    $binds[] = "$value AS $name";
                }
            }
            return parent::setBody(['query' => "CALL KEYWORDS(" . implode(",", $binds) . ")"]);
        }
        throw new RuntimeException('Index name is missing.');
    }
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
