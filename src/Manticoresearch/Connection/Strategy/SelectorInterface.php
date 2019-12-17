<?php


namespace Manticoresearch\Connection\Strategy;


interface SelectorInterface
{
    public function getConnection(array $connections);
}