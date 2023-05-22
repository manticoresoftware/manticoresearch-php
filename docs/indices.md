Indices
-------

The Nodes namespace contains methods for operations made on indices or information about them. Available for Manticore Search 3.4 or above.

Create
======
Create a new index.

`index` is mandatory.
`body` can contain:

* `columns` - definition of fields for indexes with data
* `settings` - various index settings
* `silent` - optional, if set to true, the create will not fail with an error if there is already an index with the designated name

`body` requires the presence of a `columns` array for RT and PQ indexes where keys are the field names.

Each field is an array that must contain a `type` definition.
`text` type also supports `options`, with current possible values being `indexed` and `stored`.

Index settings can be set in the `settings` parameter. Some settings can have multiple entries, like `local` for distributed 
indexes. In this case, the value of the setting will be an array of values (see the distributed index example below).

By default, the index type is `Real-Time`. For PQ or distributed indexes, the options must contain a `type` property.


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
                'settings' => [
                    'rt_mem_limit' => '256M',
                    'min_infix_len' => '3'
                ]
            ]
        ];
        $response = $client->indices()->create($params);
        
For distributed indexes, the body must contain only the `options` array, as they don't have any data (so no `columns`).

        $params = [
            'index' => 'mydistributed',
            'body' => [
                'options' => [
                    'type' => 'distributed',
                    'local' => [
                        'index1'
                        'index2`
                    ]
                ]
            ]
        ];
        $response = $client->indices()->create($params);        

       
Drop
===
Drop an existing index. `index` is mandatory.

`body` can contain the optional parameter `silent` - for not failing with an error in case the index doesn't exist.


        $params = [
            'index' => 'testrt',
            ['body' => ['silent'=>true ]]
         ];
        $response = $client->indices()->drop($params);
        
Alter
====
Alter performs changes on indexes. Currently, only adding/dropping columns are supported.
Note that the `text` type cannot be used in this call.

Expects the `index` name.

The `body` parameters:
 
* `operation` - mandatory, possible values: add, drop
* `column` - for add, drop operations, the column is an array of
    * `name` - column name
    * `type` - data type
 

        $params = [
            'index' => 'testrt',
            'body' => [
                'operation' => 'add',
                'column' => [
                    'name' => 'tag',
                    'type'=> 'string'
                ]
                   
            ]
        ];
        $response = $client->indices()->alter($params);
        
        $params = [
            'index' => 'testrt',
            'body' => [
                'operation' => 'drop',
                'column' => [
                    'name' => 'tag'
                ]
                   
            ]
        ];
        $response = $client->indices()->alter($params);        

Describe
========
Returns the structure of an index.

Expects the `index` name.

`body` is optional. It supports `pattern` as a column name for filtering the structure result.

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'columnname'
            ]
        ];
        $response = $client->indices()->describe($params);

FlushRamchunk
=============
Flushes RAM chunk for an RT index.

Expects the `index` name.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices()->flushramchunk($params);               

FlushRtindex
============
Flushes the RT index to disk.
Expects only the `index` name.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices()->flushrtindex($params);

Optimize
========

Launches optimization on the RT index. By default, the command doesn't wait for the optimize operation to finish.

Expects the `index` name.

`body` is optional. Supports the `sync` parameter - if set, the command waits for the optimization to finish.


        $params = [
            'index' => 'testrt',
            'body' => [ 'sync'=>true]
         ];
        $response = $client->indices()->optimize($params);      

Status
======
Returns statistics about the index: documents, size, chunks, as well as query statistics.

Expects the `index` name.

`body` is optional. It supports `pattern` as a property/performance metric to filter upon.

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->indices()->status($params);

Settings
========
Returns a report with the index's current settings.

Expects the `index` name.


        $params = [
            'index' => 'testrt',
        ];
        $response = $client->indices()->status($params);

Truncate
========
Truncates an index.

Expects the `index` name.
 
        $params = [
            'index' => 'testrt'
         ];
        $response = $client->indices()->truncate($params);                                                  
<!-- proofread -->