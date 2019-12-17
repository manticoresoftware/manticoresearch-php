<?php


namespace Manticoresearch\Transport;


use Manticoresearch\Connection;
use Manticoresearch\Request;
use Manticoresearch\Transport;

interface TransportInterface
{
    public function execute(Request $request,$params=[]);
    public function getConnection();
    public function setConnection(Connection $connection): Transport;
}