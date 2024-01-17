<?php


namespace Manticoresearch\Transport;

use Manticoresearch\Connection;
use Manticoresearch\Exceptions\ExceptionInterface;
use Manticoresearch\Request;
use Manticoresearch\Response;
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
	 * @return Response
	 * @throws ExceptionInterface
	 */
	public function execute(Request $request, $params = []);

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
