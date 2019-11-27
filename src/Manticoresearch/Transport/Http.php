<?php
namespace Manticoresearch\Transport;

use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Request;
use Manticoresearch\Response;

class Http extends \Manticoresearch\Transport
{

        protected  $_scheme = 'http';

        protected static $_curl;

        public function execute(Request $request,$params=[])
        {
            $connection = $this->getConnection();
            //@todo add persistent
            //@todo add custom headers
            $conn = $this->_getCurlConnection();
            $url = $connection->getScheme().'://'.$connection->getHost().':'.$connection->getPort();
            $endpoint = $request->getPath();
            $url .= $endpoint;
            $url = $this->setupURI($url,$request->getQuery());

            curl_setopt($conn, CURLOPT_URL, $url);
            curl_setopt($conn, CURLOPT_ENCODING, '');
            $data = $request->getBody();
            $method = $request->getMethod();
            $headers = [];
            array_push($headers, sprintf('Content-Type: %s', $request->getContentType()));
            if(!empty($data)) {
                $method = 'POST';

                if (is_array($data)) {
                    $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $content = $data;
                }
                curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
            }else{
                curl_setopt($conn, CURLOPT_POSTFIELDS, '');
            }
            curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
            $start = microtime(true);
            ob_start();
            curl_exec($conn);
            $responseString = \ob_get_clean();
            $end = microtime(true);
            $errorno = curl_errno($conn);
              $response = new Response($responseString,curl_getinfo($conn,CURLINFO_HTTP_CODE));
            $response->setTime($end-$start);
            //hard error
            if($errorno>0) {
                $error = curl_error($conn);
                throw new ConnectionException($error);
            }
            //soft error
            if($response->hasError()) {
                throw new ResponseException($request, $response);
            }
            return $response;
        }
        
        protected function _getCurlConnection(bool $persistent=true)
        {
            if(!$persistent || !self::$_curl) {
                self::$_curl = curl_init();
            }
            return self::$_curl;
        }
}