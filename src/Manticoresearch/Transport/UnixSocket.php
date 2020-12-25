<?php

namespace Manticoresearch\Transport;

use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Request;
use Manticoresearch\Response;

/**
 * Class UnixSocket
 * @package Manticoresearch\Transport
 */
class UnixSocket extends \Manticoresearch\Transport implements TransportInterface
{
	/**
	 * @var string
	 */
	protected $scheme = 'http';
	/**
	 * @var \Swoole\Client|null
	 */
	protected static ?\Swoole\Client $client = null;

	/**
	 * @param Request $request
	 * @param array   $params
	 *
	 * @return Response
	 */
	public function execute(Request $request, $params = [])
	{
		$connection = $this->getConnection();
		$client     = $this->getSocketConnection($connection->getConfig('persistent'));
		$endpoint   = $request->getPath();

		$url = $this->setupURI($endpoint, $request->getQuery());

		$data      = $request->getBody();
		$method    = $request->getMethod();
		$headers   = $connection->getHeaders();
		$headers[] = sprintf('Content-Type: %s', $request->getContentType());
		if (!empty($data))
		{
			if (is_array($data))
			{
				$content = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
			else
			{
				$content = $data;
			}
		}

		if ($connection->getConnectTimeout() > 0)
		{
			//TODO time out
		}

		if ($connection->getConfig('username') !== null && $connection->getConfig('password') !== null)
		{
			//TODO: http auth
			// $connection->getConfig('username')
			// $connection->getConfig('password')
		}

		if ($connection->getConfig('proxy') !== null)
		{
			//TODO http proxy
		}

		$start = microtime(true);

		$client->connect($connection->getHost());
		$http_request = $this->create_http_request($method, $url, $content, $headers);
		$client->send($http_request);
		$socket_response  = $client->recv();
		$response_headers = $this->parse_http_response($socket_response);
		$client->close(true);

		$responseString = $response_headers['body'];

		$end = microtime(true);

		// TODO error
		$errorno = 0;
		$status  = $response_headers['status'];

		if (isset($params['responseClass']))
		{
			$responseClass = $params['responseClass'];
			$response      = new $responseClass($responseString, $status);
		}
		else
		{
			$response = new Response($responseString, $status);
		}

		$time = $end - $start;
		$response->setTime($time);
		$response->setTransportInfo([
			'url'     => $url,
			'headers' => $headers,
			'body'    => $request->getBody()
		]);
		//hard error
		if ($errorno > 0)
		{
			$error = curl_error($conn);

			/* @phpstan-ignore-next-line */
			self::$curl = false;
			throw new ConnectionException($error, $request);
		}

		$this->logger->debug('Request body:', [
			'connection' => $connection->getConfig(),
			'payload'    => $request->getBody()
		]);
		$this->logger->info('Request:', [
			'url'    => $url,
			'status' => $status,
			'time'   => $time
		]);
		$this->logger->debug('Response body:', [json_decode($responseString, true)]);
		//soft error
		if ($response->hasError())
		{
			$this->logger->error('Response error:', [$response->getError()]);
			throw new ResponseException($request, $response);
		}

		return $response;
	}

	/**
	 * @param bool $persistent
	 *
	 * @return \Swoole\Client|null
	 */
	protected function getSocketConnection(bool $persistent = true)
	{
		if (!$persistent || !self::$client)
		{
			self::$client = new \Swoole\Client(SWOOLE_SOCK_UNIX_STREAM, false);

			self::$client->set([
				// 'open_eof_check' => true,
				// 'open_eof_split' => true,
				// 'package_eof'    => "\r\n\r\n"
			]);
		}

		return self::$client;
	}

	/**
	 * @param string      $method
	 * @param string      $url
	 * @param string|null $content
	 * @param array       $headers
	 *
	 * @return string
	 */
	public function create_http_request(string $method, string $url, ?string $content = null, array $headers = [])
	{
		$pack = [];

		// HTTP/1.1 couse an implementation problem on manticore api side
		$pack[] = "${method} ${url} HTTP/1.0";
		$pack[] = "Accept: */*";
		$pack   = array_merge($pack, $headers);
		if ($content)
		{
			$pack[] = 'Content-Length: ' . strlen($content);
		}
		$pack[] = "";

		if ($content)
		{
			$pack[] = $content;
		}

		$pack[] = "\r\n";

		return implode("\r\n", $pack);
	}

	/**
	 * @param string $raw_response_string
	 *
	 * @return array
	 */
	public function parse_http_response(string $raw_response_string)
	{
		$return = [
			'http_version' => null,
			'status'       => 0,
			'status_text'  => '',
			'headers'      => [],
			'body'         => null,
		];

		$raw_response_arr = explode("\r\n\r\n", $raw_response_string, 2);

		$headers = explode("\r\n", $raw_response_arr[0]);

		if (str_starts_with($headers[0], 'HTTP'))
		{
			[$return['http_version'], $return['status'], $return['status_text']] = explode(' ', $headers[0], 3);
			unset($headers[0]);
		}

		foreach ($headers as $v)
		{
			$h = preg_split('/:\s*/', $v, flags: PREG_SPLIT_NO_EMPTY);

			if (isset($h[0]))
			{
				$return['headers'][strtolower($h[0])] = $h[1];
			}
		}

		if ($return['headers']['content-length'])
		{
			$return['headers']['content-length'] = (int)$return['headers']['content-length'];
			$return['original_body']             = $raw_response_arr[1];
			$return['body']                      = substr($raw_response_arr[1], 0, $return['headers']['content-length']);
		}

		$return['status'] = (int)$return['status'];

		return $return;
	}
}
