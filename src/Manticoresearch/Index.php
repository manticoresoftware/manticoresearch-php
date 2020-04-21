<?php


namespace Manticoresearch;

use Manticoresearch\Exceptions\RuntimeException;

/**
 * Manticore index object
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
class Index
{
    protected $_client;
    protected $_index;

    public function __construct(Client $client, $index = null)
    {
        $this->_client = $client;

        $this->_index = $index;
    }

    public function search($input): Search
    {
        $search = new Search($this->_client);
        $search->setIndex($this->_index);
        return $search->search($input);
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
        $result = new ResultSet($this->_client->search($params, true));
        return $result->valid() ? $result->current() : null;
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

    public function addDocuments($documents)
    {
        $toinsert = [];
        foreach ($documents as $document) {
            $id = $document['id'];
            unset($document['id']);
            $toinsert[] = [
                'insert' => [
                    'index' => $this->_index,
                    'id' => $id,
                    'doc' => $document
                ]
            ];
        }
        return $this->_client->bulk(['body' => $toinsert]);
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
        if($query instanceof Query) {
            $query = $query->toArray();
        }
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
        if($query instanceof Query) {
            $query = $query->toArray();
        }
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

    public function replaceDocuments($documents)
    {
        $toreplace = [];
        foreach ($documents as $document) {
            $id = $document['id'];
            unset($document['id']);
            $toreplace[] = [
                'replace' => [
                    'index' => $this->_index,
                    'id' => $id,
                    'doc' => $document
                ]
            ];
        }
        return $this->_client->bulk(['body' => $toreplace]);
    }

    public function create($fields, $settings = [],$silent=false)
    {
        $params = [
            'index' => $this->_index,
            'body' => [
                'columns' => $fields,
                'settings' => $settings
            ]
        ];
        if($silent===true) {
            $params['body']['silent'] = true;
        }
        return $this->_client->indices()->create($params);
    }

    public function drop($silent = false)
    {
        $params = [
            'index' => $this->_index,
        ];
        if ($silent === true) {
            $params['body'] = ['silent' => true];
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

    public function status()
    {
        $params = [
            'index' => $this->_index,
        ];
        return $this->_client->indices()->status($params);
    }

    public function truncate()
    {
        $params = [
            'index' => $this->_index,
        ];
        return $this->_client->indices()->truncate($params);
    }

    public function optimize($sync = false)
    {
        $params = [
            'index' => $this->_index,
        ];
        if ($sync === true) {
            $params['body'] = ['sync' => true];
        }
        return $this->_client->indices()->optimize($params);
    }

    public function flush()
    {
        $params = [
            'index' => $this->_index,
        ];
        $this->_client->indices()->flushrtindex($params);
    }

    public function flushramchunk()
    {
        $params = [
            'index' => $this->_index,
        ];
        $this->_client->indices()->flushramchunk($params);
    }

    public function alter($operation,$name, $type)
    {
        if($operation==='add') {
            $params = [
                'index' => $this->_index,
                'body' => [
                    'operation' => 'add',
                    'column' => ['name' => $name, 'type' => $type]
                ]
            ];

        }elseif($operation==='drop'){
            $params = [
                'index' => $this->_index,
                'body' => [
                    'operation' => 'drop',
                    'column' => ['name' => $name]
                ]
            ];
        } else {
            throw new RuntimeException('Alter operation not recognized');
        }
        return $this->_client->indices()->alter($params);
    }

    public function keywords($query, $options)
    {
        $params = [
            'index' => $this->_index,
            'body' => [
                '$query' => $query,
                'options' => $options
            ]
        ];
        return $this->_client->keywords($params);
    }

    public function suggest($query, $options)
    {
        $params = [
            'index' => $this->_index,
            'body' => [
                '$query' => $query,
                'options' => $options
            ]
        ];
        return $this->_client->suggest($params);

    }

    public function getClient(): Client
    {
        return $this->_client;
    }

    public function getName(): string
    {
        return $this->_index;
    }

    public function setName($index): self
    {
        $this->_index = $index;
        return $this;
    }
}
