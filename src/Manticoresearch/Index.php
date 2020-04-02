<?php


namespace Manticoresearch;


class Index
{
    protected $_client;
    protected $_index;

    public function __construct(Client $client, $index = null)
    {
        $this->_client = $client;

        $this->_index = $index;
    }

    public function search($string, $fields = null): Search
    {
        $search = new Search($this->_client);
        $search->setIndex($this->_index);
        return $search->search($string, $fields);
    }

    public function getDocumentById($id)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'query' => [
                    'equals' => ['id' => $id]
                ]
            ]
        ];
        return $this->_client->search($params);
    }

    public function addDocument($data, $id = null)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'id' => $id,
                'doc' => $data
            ]
        ];
        return $this->_client->insert($params);
    }

    public function deleteDocument($id)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'id' => $id
            ]
        ];
        return $this->_client->delete($params);
    }

    public function deleteDocuments($query)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'query' => $query
            ]
        ];
        return $this->_client->delete($params);
    }

    public function updateDocument($data, $id)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'id' => $id,
                'doc' => $data
            ]
        ];
        return $this->_client->update($params);
    }

    public function updateDocuments($data, $query)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'query' => $query,
                'doc' => $data
            ]
        ];
        return $this->_client->update($params);
    }

    public function replaceDocument($data, $id)
    {
        $params = [
            'body' => [
                'index' => $this->_index,
                'id' => $id,
                'doc' => $data
            ]
        ];
        return $this->_client->replace($params);
    }

    public function create($fields, $settings)
    {
        $params = [
            'index' => $this->_index,
            'body' => [
                'columns' => $fields,
                'settings' => $settings
            ]
        ];
        return $this->_client->indices()->create($params);
    }

    public function drop($silent=false)
    {
        $params = [
            'index' => $this->_index,
        ];
        if($silent===true) {
            $params['body'] = ['silent'=>true];
        }
        return $this->_client->indices()->drop($params);
    }
    public function describe()
    {
        $params = [
            'index' => $this->_index,
        ];
        return $this->_client->indices()->describe($params);
    }
    public function truncate()
    {
        $params = [
            'index' => $this->_index,
        ];
        return $this->_client->indices()->truncate($params);
    }
    public function getClient():Client
    {
        return $this->_client;
    }

    public function getName():string
    {
        return $this->_index;
    }

    public function setName($index):self
    {
        $this->_index = $index;
        return $this;
    }
}