<?php


namespace Manticoresearch;


class Transport
{
    protected $_connection;

    public function __construct(Connection $connection = null)
    {
        if ($connection) {
            $this->_connection = $connection;
        }
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function setConnection(Connection $connection): Transport
    {
        $this->_connection = $connection;
        return $this;
    }

    public static function create($transport, Connection $connection, array $params = [])
    {
        $className = "Manticoresearch\\Transport\\$transport";
        if (class_exists($className)) {
            $transport = new $className($connection);
        }
        if ($transport instanceof self) {
            $transport->setConnection($connection);
        } else {
            throw new \Exception('Bad transport');
        }
        return $transport;
    }

    protected function setupURI(string $uri, $query = []): string
    {
        if (!empty($query)) {
            foreach ($query as $k => $v) {
                if (is_bool($query)) {
                    $query[$k] = $v ? 'true' : 'false';
                }
            }
            $uri = $uri . '?' . http_build_query($query);
        }
        return $uri;
    }
}