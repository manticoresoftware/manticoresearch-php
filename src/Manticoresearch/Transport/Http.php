<?php
namespace Manticoresearch\Transport;

use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Request;
use Manticoresearch\Response;

/**
 * Class Http
 * @package Manticoresearch\Transport
 */
class Http extends \Manticoresearch\Transport implements TransportInterface
{

    /**
     * @var string
     */
    protected $_scheme = 'http';

    /**
     * @var object
     */
    protected static $_curl;

    /**
     * @param Request $request
     * @param array $params
     * @return Response
     */
    public function execute(Request $request, $params=[])
    {
        $connection = $this->getConnection();
        //@todo add persistent
        //@todo add custom headers
        $conn = $this->_getCurlConnection($connection->getConfig('persistent'));
        $url = $this->_scheme.'://'.$connection->getHost().':'.$connection->getPort();
        $endpoint = $request->getPath();
        $url .= $endpoint;
        $url = $this->setupURI($url, $request->getQuery());

        curl_setopt($conn, CURLOPT_URL, $url);
        curl_setopt($conn, CURLOPT_TIMEOUT, $connection->getTimeout());
        curl_setopt($conn, CURLOPT_ENCODING, '');
        curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);
        $data = $request->getBody();
        $method = $request->getMethod();
        $headers = $connection->getHeaders();
        $headers[] = sprintf('Content-Type: %s', $request->getContentType());
        if (!empty($data)) {
            if (is_array($data)) {
                $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $content = $data;
            }
            curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
        } else {
            curl_setopt($conn, CURLOPT_POSTFIELDS, '');
        }
        curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);

        if ($connection->getConnectTimeout()>0) {
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $connection->getConnectTimeout());
        }

        if ($connection->getConfig('username') !== null && $connection->getConfig('password') !== null) {
            curl_setopt($conn, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($conn, CURLOPT_USERPWD, $connection->getConfig('username').":".$connection->getConfig('password'));
        }
        if ($connection->getConfig('proxy') !== null) {
            curl_setopt($conn, CURLOPT_PROXY, $connection->getConfig('proxy'));
        }
        if (!empty($connection->getConfig('curl'))) {
            foreach ($connection->getConfig('curl') as $k=>$v) {
                curl_setopt($conn, $k, $v);
            }
        }
        $start = microtime(true);
        ob_start();
        curl_exec($conn);
        $responseString = \ob_get_clean();
        $end = microtime(true);
        $errorno = curl_errno($conn);
        $status = curl_getinfo($conn, CURLINFO_HTTP_CODE);
        if (isset($params['responseClass'])) {
            $responseClass = $params['responseClass'];
            $response = new $responseClass($responseString, $status);
        } else {
            $response = new Response($responseString, $status);
        }

        $time = $end-$start;
        $response->setTime($time);
        $response->setTransportInfo([
                'url' => $url,
                'headers' => $headers,
                'body' => $request->getBody()
            ]);
        //hard error
        if ($errorno>0) {
            $error = curl_error($conn);
            self::$_curl = false;
            throw new ConnectionException($error, $request);
        }


        $this->_logger->debug('Request body:', [
                'connection' => $connection->getConfig(),
                'payload'=> $request->getBody()
            ]);
        $this->_logger->info(
            'Request:',
            [
                    'url' => $url,
                    'status' => $status,
                    'time' => $time
                ]
        );
        $this->_logger->debug('Response body:', [json_decode($responseString, true)]);
        //soft error
        if ($response->hasError()) {
            $this->_logger->error('Response error:', [$response->getError()]);
            throw new ResponseException($request, $response);
        }
        return $response;
    }

    /**
     * @param bool $persistent
     * @return false|resource
     */
    protected function _getCurlConnection(bool $persistent=true)
    {
        if (!$persistent || !self::$_curl) {
            self::$_curl = curl_init();
        }
        return self::$_curl;
    }
}
