<?php


namespace Manticoresearch;


use Psr\Log\LoggerInterface;

/**
 * Class Connection
 * @package Manticoresearch
 */
class Connection
{
    /**
     * @var array
     */
    protected $config;
    /**
     * @var bool
     */
    protected $_alive;
/*
 * $params['transport']  = transport class name
 * $params['host']       = hostname
 * $params['port']       = port number
 * $params['timeout']    = connection timeout
 * $params['connect_timeout'] = connection connect timeout
 * $params['proxy']       = proxy host:port string
 * $params['username']  = username for http auth
 * $params['password']  = password for http auth
 * $params['headers']   = array of custom headers
 * $params['curl']      = array of pairs of curl option=>value
 * $params['persistent'] = bool if connection is persistent
 */
    /**
     * Connection constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->config = array(
            'transport' => 'Http',
            'host' => '127.0.0.1',
            'scheme' => 'http',
            'port' => '9308',
            'timeout' => 300,
            'connect_timeout' => 0,
            'proxy' => null,
            'username' => null,
            'password' => null,
            'headers' => [],
            'curl' => [],
            'persistent' => true
        );
        $this->config = array_merge($this->config, $params);
        $this->_alive = true;
    }

    /**
     * @param $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->config['host'] = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->config['host'];
    }

    /**
     * @param $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->config['port'] = (int)$port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->config['port'];
    }

    /**
     * @param $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->config['timeout'] = (int)$timeout;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->config['headers'];
    }

    /**
     * @param $headers
     * @return $this
     */
    public function setheaders($headers)
    {
        $this->config['headers'] = $headers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->config['timeout'];
    }

    /**
     * @param $connecttimeout
     * @return $this
     */
    public function setConnectTimeout($connecttimeout)
    {
        $this->config['connect_timeout'] = (int)$connecttimeout;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnectTimeout()
    {
        return $this->config['connect_timeout'];
    }

    /**
     * @param $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->config['transport'] = $transport;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransport()
    {
        return $this->config['transport'];
    }

    /**
     * @param LoggerInterface
     * @return mixed
     * @throws \Exception
     */
    public function getTransportHandler(LoggerInterface $logger)
    {
        return Transport::create($this->getTransport(), $this,$logger);
    }

    /**
     * @param $config
     * @return $this
     */
    public function setConfig($config)
    {
        foreach ($config as $ckey => $cvalue) {
            $this->config[$ckey] = $cvalue;
        }
        return $this;
    }

    /**
     * @param string|null
     * @return mixed|null
     */
    public function getConfig($key =  null)
    {
        if($key == null) {
            return $this->config;
        }
        if (isset($this->config[$key])) {
            return $this->config[$key];
        } else {
            return null;
        }
    }

    /**
     * @param array $params
     * @return array|static
     */
    public static function create($params = [])
    {
        if (is_array($params)) {
            return new static($params);
        }
        if ($params instanceof self) {
            return $params;
        }
    }

    /**
     * @return bool
     */
    public function isAlive(): bool
    {
        return $this->_alive;
    }

    /**
     * @param bool $state
     */
    public function mark(bool $state)
    {
        $this->_alive = $state;
    }
}