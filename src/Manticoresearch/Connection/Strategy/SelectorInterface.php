<?php


namespace Manticoresearch\Connection\Strategy;


use Manticoresearch\Connection;

/**
 * Interface SelectorInterface
 * @package Manticoresearch\Connection\Strategy
 */
interface SelectorInterface
{
    /**
     * @param array $connections
     * @return mixed
     */
    public function getConnection(array $connections):Connection;
}