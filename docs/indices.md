Indices
-------

Nodes namespace contains methods for operations made on indices or information about them. Available for  Manticore Search 3.4 or above.

Create
======
Create a new index.

`index` is mandatory.
`body` can contain:

* `columns` - definition of fields for indexes with data
* `options` - various index settings
* `silent` -  if set to true, the create will not fail with error if there is already an index with the designated name

`body` require presence of `columns`  array for RT and PQ indexes where keys are the column names. Each column requires a  `type` defined.
`text` type support 'indexed' and 'stored' options.
Index settings can be set in `options` parameter. By default, the index type is Real-Time. For PQ or distributed indexes, the options must contain a `type` property.
 

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
        
For distributed indexes, the body must have only the `options` array, since they don't have any data (so no `columns`).

        $params = [
            'index' => 'mydistributed',
            'body' => [
                'options' => [
                    'type' => 'distributed',
                    'local' => 'index1'
                    'local' => 'index2`
                ]
            ]
        ];
        $response = $client->indices->create($params);        

       
Drop
===
Drop an existing index. `index` is mandatory.

`body` can contain optional parameter `silent` - for not failing with error in case the index doesn't exist.


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