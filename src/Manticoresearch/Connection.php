<?php


namespace Manticoresearch;


class Connection
{
    protected $config;
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
            'persistent' => false
        );
        $this->config = array_merge($this->config, $params);
        $this->_alive = true;
    }

    public function setHost($host)
    {
        $this->config['host'] = $host;
        return $this;
    }

    public function getHost()
    {
        return $this->config['host'];
    }

    public function setPort($port)
    {
        $this->config['port'] = (int)$port;
        return $this;
    }

    public function getPort()
    {
        return $this->config['port'];
    }

    public function setTimeout($timeout)
    {
        $this->config['timeout'] = (int)$timeout;
        return $this;
    }

    public function getHeaders()
    {
        return $this->config['headers'];
    }

    public function setheaders($headers)
    {
        $this->config['headers'] = $headers;
        return $this;
    }

    public function getTimeout()
    {
        return $this->config['timeout'];
    }

    public function setConnectTimeout($connecttimeout)
    {
        $this->config['connect_timeout'] = (int)$connecttimeout;
        return $this;
    }

    public function getConnectTimeout()
    {
        return $this->config['connect_timeout'];
    }

    public function setTransport($transport)
    {
        $this->config['transport'] = $transport;
        return $this;
    }

    public function getTransport()
    {
        return $this->config['transport'];
    }

    public function getTransportHandler()
    {
        return Transport::create($this->getTransport(), $this);
    }

    public function setConfig($config)
    {
        foreach ($config as $ckey => $cvalue) {
            $this->config[$ckey] = $cvalue;
        }
        return $this;
    }

    public function getConfig($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        } else {
            return null;
        }
    }

    public static function create($params = [])
    {
        if (is_array($params)) {
            return new static($params);
        }
        if ($params instanceof self) {
            return $params;
        }
    }

    public function isAlive(): bool
    {
        return $this->_alive;
    }

    public function mark(bool $state)
    {
        $this->_alive = $state;
    }
}