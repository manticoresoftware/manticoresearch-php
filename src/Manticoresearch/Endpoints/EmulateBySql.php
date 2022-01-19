<?php


namespace Manticoresearch\Endpoints;

class EmulateBySql extends Sql
{
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->setMode('raw');
        $this->params = ['responseClass' => 'Manticoresearch\\Response\\Sql'];
    }
}
