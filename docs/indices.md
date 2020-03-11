Indices
-------

Nodes namespace contains methods for  operations made on indices or information about them.

Create
======
Create a new index.

`index` is mandatory.
`body` require presence of `columns`  array where keys are the column names. Each column requires a  `type` defined.
`text` type support 'indexed' and 'stored' options.
Index settings can be set in `options` parameter.
 

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
Drop an existing index. `index` is mandatory.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices->drop($params);
        
Alter
====
Alter perform changes on indexes. Currently supported only add/drop columns.
`body` parameters:
 
* `operation` -  mandatory, possible values: add,drop
* `column` -  for add,drop operations the column is an array of
    * `name` -  column name
    * `type` - data type
 

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

`body` is optional. It support `pattern` as a column name for filtering the structure result.

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

Launch optimization on RT index. The command doesn't wait by default for the optimize operation to finish. 

`body` is optional. Supports `sync` parameter - if set, the command waits for the optimize to finish.


        $params = [
            'index' => 'testrt',
            'body' => [ 'sync'=>true]
         ];
        $response = $client->indices->optimize($params);      

Status
======
Return statistics about index: documents, size, chunks, as well as query statistics. 

`body` is optional. It support `pattern` as a property/performance metric to filter upon.

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->indices->status($params);             
Truncate
========
Truncates an index. 
 
        $params = [
            'index' => 'testrt'
         ];
        $response = $client->indices->truncate($params);                                                  