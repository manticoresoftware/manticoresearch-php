<?php


namespace Manticoresearch\Transport;


use Manticoresearch\Connection;
use Manticoresearch\Request;
use Manticoresearch\Transport;

/**
 * Interface TransportInterface
 * @package Manticoresearch\Transport
 */
interface TransportInterface
{
    /**
     * @param Request $request
     * @param array $params
     * @return mixed
     */
    public function execute(Request $request, $params=[]);

    /**
     * @return mixed
     */
    public function getConnection();

    /**
     * @param Connection $connection
     * @return Transport
     */
    public function setConnection(Connection $connection): Transport;
}