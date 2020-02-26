Indices
-------

Indices namespace contains operations made on indices.

Create
======
Create a new index.

        $params = [
            'index' => 'testrt',
            'body' => [
                'columns' => [
                    'title' => [
                        'type' => 'text',
                        'options' => ['indexed', 'stored']
                    ],
                    'gid' => [
                        'type' => 'integer'
                    ]
                ],
                'options' => [
                    'rt_mem_limit' => '256M',
                    'min_infix_len' => '3'
                ]
            ]
        ];
        $response = $client->indices->create($params);
        
Drop
===
Drop an existing index

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices->drop($params);
        
Alter
====
Alter perform changes on indexes. Currently supported only add/drop columns.

        $params = [
            'index' => 'testrt',
            'body' => [
                'operation' => 'add',
                'column' => [
                    'name' => 'tag'
                    'type'=> 'string'
                ]
                   
            ]
        ];
        $response = $client->indices->alter($params);
        
        $params = [
            'index' => 'testrt',
            'body' => [
                'operation' => 'drop',
                'column' => [
                    'name' => 'tag'
                ]
                   
            ]
        ];
        $response = $client->indices->alter($params);        

Describe
========
Returns structure of an index.

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'columnname'
            ]
        ];
        $response = $client->indices->describe($params);

FlushRamchunk
=============
Flushed RAM chunk for a RT index.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices->flushramchunk($params);               

FlushRtindex
============
Flushed the RT index to disk.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices->flushrtindex($params);

Optimize
========

Perform optimization on RT index. If sync=true, the command waits for execution to finish, otherwise it's launched in background.

        $params = [
            'index' => 'testrt',
            'body' => [ 'sync'=>true]
         ];
        $response = $client->indices->optimize($params);      

Status
======
Return statistics about index: documents, size, chunks, as well as query statistics. 

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->indices->status($params);             
Truncate
========

        $params = [
            'index' => 'testrt',
            'body' => [ 'with'=>'reconfigure']
         ];
        $response = $client->indices->truncate($params);                                                  