<?php


namespace Manticoresearch;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Query\Percolate;
use Manticoresearch\Results;

/**
 * Manticore index object
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
class Index
{
    protected $client;
    protected $index;
    protected $cluster;

    public function __construct(Client $client, $index = null)
    {
        $this->client = $client;

        $this->index = $index;

        $this->cluster = null;
    }

    public function search($input): Search
    {
        $search = new Search($this->client);
        $search->setIndex($this->index);
        return $search->search($input);
    }

    public function getDocumentById($id)
    {
        $params = [
            'body' => [
                'index' => $this->index,
                'query' => [
                    'equals' => ['id' => $id]
                ]
            ]
        ];
        $result = new ResultSet($this->client->search($params, true));
        return $result->valid() ? $result->current() : null;
    }

    public function getDocumentByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $params = [
            'body' => [
                'index' => $this->index,
                'query' => [
                    'in' => ['id' => $ids]
                ]
            ]
        ];
        return new ResultSet($this->client->search($params, true));
    }

    public function addDocument($data, $id = 0)
    {
        if (is_object($data)) {
            $data = (array) $data;
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        }
        $params = [
            'body' => [
                'index' => $this->index,
                'id' => $id,
                'doc' => $data
            ]
        ];

        if ($this->cluster !== null) {
            $params['body']['cluster'] = $this->cluster;
        }
        return $this->client->insert($params);
    }

    public function addDocuments($documents)
    {
        $toinsert = [];
        foreach ($documents as $document) {
            if (is_object($document)) {
                $document = (array) $document;
            } elseif (is_string($document)) {
                $document = json_decode($document, true);
            }
            if (isset($document['id'])) {
                $id = $document['id'];
                unset($document['id']);
            } else {
                $id = 0;
            }
            $insert = [
                'index' => $this->index,
                'id' => $id,
                'doc' => $document
            ];
            if ($this->cluster !== null) {
                $insert['cluster'] = $this->cluster;
            }
            $toinsert[] = ['insert' => $insert];
        }
        return $this->client->bulk(['body' => $toinsert]);
    }

    public function deleteDocument($id)
    {
        $params = [
            'body' => [
                'index' => $this->index,
                'id' => $id
            ]
        ];
        if ($this->cluster !== null) {
            $params['body']['cluster'] = $this->cluster;
        }
        return $this->client->delete($params);
    }

    public function deleteDocuments($query)
    {
        if ($query instanceof Query) {
            $query = $query->toArray();
        }
        $params = [
            'body' => [
                'index' => $this->index,
                'query' => $query
            ]
        ];
        if ($this->cluster !== null) {
            $params['body']['cluster'] = $this->cluster;
        }
        return $this->client->delete($params);
    }

    public function updateDocument($data, $id)
    {
        $params = [
            'body' => [
                'index' => $this->index,
                'id' => $id,
                'doc' => $data
            ]
        ];
        if ($this->cluster !== null) {
            $params['body']['cluster'] = $this->cluster;
        }
        return $this->client->update($params);
    }

    public function updateDocuments($data, $query)
    {
        if ($query instanceof Query) {
            $query = $query->toArray();
        }
        $params = [
            'body' => [
                'index' => $this->index,
                'query' => $query,
                'doc' => $data
            ]
        ];
        if ($this->cluster !== null) {
            $params['body']['cluster'] = $this->cluster;
        }
        return $this->client->update($params);
    }

    public function replaceDocument($data, $id)
    {
        if (is_object($data)) {
            $data = (array) $data;
        } elseif (is_string($data)) {
            $data = json_decode($data, true);
        }
        $params = [
            'body' => [
                'index' => $this->index,
                'id' => $id,
                'doc' => $data
            ]
        ];
        if ($this->cluster !== null) {
            $params['body']['cluster'] = $this->cluster;
        }
        return $this->client->replace($params);
    }

    public function replaceDocuments($documents)
    {
        $toreplace = [];
        foreach ($documents as $document) {
            if (is_object($document)) {
                $document = (array) $document;
            } elseif (is_string($document)) {
                $document = json_decode($document, true);
            }
            $id = $document['id'];
            unset($document['id']);
            $replace = [
                'index' => $this->index,
                'id' => $id,
                'doc' => $document

            ];
            if ($this->cluster !== null) {
                $replace['cluster'] = $this->cluster;
            }
            $toreplace[] = ['replace' => $replace];
        }
        return $this->client->bulk(['body' => $toreplace]);
    }

    public function create($fields, $settings = [], $silent = false)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'columns' => $fields,
                'settings' => $settings
            ]
        ];
        if ($silent === true) {
            $params['body']['silent'] = true;
        }
        return $this->client->indices()->create($params);
    }

    public function drop($silent = false)
    {
        $params = [
            'index' => $this->index,
        ];
        if ($silent === true) {
            $params['body'] = ['silent' => true];
        }
        return $this->client->indices()->drop($params);
    }

    public function describe()
    {
        $params = [
            'index' => $this->index,
        ];
        return $this->client->indices()->describe($params);
    }

    public function status()
    {
        $params = [
            'index' => $this->index,
        ];
        return $this->client->indices()->status($params);
    }

    public function truncate()
    {
        $params = [
            'index' => $this->index,
        ];
        return $this->client->indices()->truncate($params);
    }

    public function optimize($sync = false)
    {
        $params = [
            'index' => $this->index,
        ];
        if ($sync === true) {
            $params['body'] = ['sync' => true];
        }
        return $this->client->indices()->optimize($params);
    }

    public function flush()
    {
        $params = [
            'index' => $this->index,
        ];
        $this->client->indices()->flushrtindex($params);
    }

    public function flushramchunk()
    {
        $params = [
            'index' => $this->index,
        ];
        $this->client->indices()->flushramchunk($params);
    }

    public function alter($operation, $name, $type = null)
    {
        if ($operation === 'add') {
            $params = [
                'index' => $this->index,
                'body' => [
                    'operation' => 'add',
                    'column' => ['name' => $name, 'type' => $type]
                ]
            ];
        } elseif ($operation === 'drop') {
            $params = [
                'index' => $this->index,
                'body' => [
                    'operation' => 'drop',
                    'column' => ['name' => $name]
                ]
            ];
        } else {
            throw new RuntimeException('Alter operation not recognized');
        }
        return $this->client->indices()->alter($params);
    }

    public function keywords($query, $options)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => $query,
                'options' => $options
            ]
        ];
        return $this->client->keywords($params);
    }

    public function suggest($query, $options)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => $query,
                'options' => $options
            ]
        ];
        return $this->client->suggest($params);
    }

    public function explainQuery($query)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => $query,
            ]
        ];
        return $this->client->explainQuery($params);
    }


    public function percolate($docs)
    {
        $params = ['index' => $this->index, 'body' => []];
        if ($docs instanceof Percolate) {
            $params['body']['query'] = $docs->toArray();
        } else {
            if (isset($docs[0]) && is_array($docs[0])) {
                $params['body']['query'] = ['percolate' => ['documents' => $docs]];
            } else {
                $params['body']['query'] = ['percolate' => ['document' => $docs]];
            }
        }
        return new Results\PercolateResultSet($this->client->pq()->search($params, true));
    }

    public function percolateToDocs($docs)
    {
        $params = ['index' => $this->index, 'body' => []];
        if ($docs instanceof Percolate) {
            $params['body']['query'] = $docs->toArray();
        } else {
            if (isset($docs[0]) && is_array($docs[0])) {
                $params['body']['query'] = ['percolate' => ['documents' => $docs]];
            } else {
                $params['body']['query'] = ['percolate' => ['document' => $docs]];
            }
        }
        return new Results\PercolateDocsResultSet($this->client->pq()->search($params, true), $docs);
    }


    public function getClient(): Client
    {
        return $this->client;
    }

    public function getName(): string
    {
        return $this->index;
    }

    public function setName($index): self
    {
        $this->index = $index;
        return $this;
    }

    public function setCluster($cluster): self
    {
        $this->cluster = $cluster;
        return $this;
    }
}
