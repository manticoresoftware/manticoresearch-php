<?php


namespace Manticoresearch\Endpoints;


class EmulateBySql extends Sql
{
    public function __construct($params = [])
    {
        parent::__construct($params);
        parent::setMode('raw');
    }
}