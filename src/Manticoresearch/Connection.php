<?php


namespace Manticoresearch;


class Connection
{
    protected $config;
    protected $_alive;
    public function __construct( array $params)
    {
        $this->config = array(
            'host' => '127.0.0.1',
            'port' => '9308',
            'scheme' => 'http',
            'proxy' => false,
            'compression' => false,
            'transport' => 'Http'
        );
        $this->config = array_merge($this->config,$params);
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
        $this->config['port'] = (int) $port;
        return $this;
    }

    public function getPort()
    {
        return $this->config['port'];
    }

    public function setScheme($scheme)
    {
        $this->config['scheme'] = $scheme;
        return $this;
    }

    public function getScheme()
    {
        return $this->config['scheme'];
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
        return Transport::create($this->getTransport(),$this);
    }
    public function setConfig($config)
    {
        foreach($config as $ckey => $cvalue)
        {
            $this->config[$ckey] = $cvalue;
        }
        return $this;
    }
    public function getConfig($key)
    {
        if(isset($this->config[$key])) {
            return $this->config[$key];
        }else{
            return null;
        }
    }
    public static function create($params = [])
    {
        if(is_array($params))
        {
            return new static($params);
        }
        if($params instanceof self) {
            return $params;
        }
    }
    public function isAlive():bool
    {
        return $this->_alive;
    }
    public function mark(bool $state)
    {
        $this->_alive = $state;
    }
}