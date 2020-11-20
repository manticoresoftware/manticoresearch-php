Indices
-------

Nodes namespace contains methods for operations made on indices or information about them. Available for  Manticore Search 3.4 or above.

Create
======
Create a new index.

`index` is mandatory.
`body` can contain:

* `columns` - definition of fields for indexes with data
* `settings` - various index settings
* `silent` -  optional, if set to true, the create will not fail with error if there is already an index with the designated name

`body` require presence of `columns`  array for RT and PQ indexes where keys are the field names.

Each field is an array that must contain `type` defined.
`text` type also support `options`, current possible values are `indexed` and `stored`.

Index settings can be set in `settings` parameter. Some settings can have multiple entries, like `local` for distributed 
indexes. In this case the value of the setting will be an array of values (see distributed index example below).

By default, the index type is Real-Time. For PQ or distributed indexes, the options must contain a `type` property.
 

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
        
For distributed indexes, the body must have only the `options` array, since they don't have any data (so no `columns`).

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

`body` can contain optional parameter `silent` - for not failing with error in case the index doesn't exist.


        $params = [
            'index' => 'testrt',
            ['body' => ['silent'=>true ]]
         ];
        $response = $client->indices()->drop($params);
        
Alter
====
Alter perform changes on indexes. Currently supported only add/drop columns.
Note that `text` type cannot be used in this call.

Expects `index` name.

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
Returns structure of an index.

Expects `index` name.

`body` is optional. It support `pattern` as a column name for filtering the structure result.

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'columnname'
            ]
        ];
        $response = $client->indices()->describe($params);

FlushRamchunk
=============
Flushed RAM chunk for a RT index.

Expects `index` name.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices()->flushramchunk($params);               

FlushRtindex
============
Flushed the RT index to disk.
Expects only `index` name.

        $params = [
            'index' => 'testrt',
         ];
        $response = $client->indices()->flushrtindex($params);

Optimize
========

Launch optimization on RT index. The command doesn't wait by default for the optimize operation to finish. 

Expects `index` name.

`body` is optional. Supports `sync` parameter - if set, the command waits for the optimize to finish.


        $params = [
            'index' => 'testrt',
            'body' => [ 'sync'=>true]
         ];
        $response = $client->indices()->optimize($params);      

Status
======
Return statistics about index: documents, size, chunks, as well as query statistics.

Expects `index` name. 

`body` is optional. It support `pattern` as a property/performance metric to filter upon.

        $params = [
            'index' => 'testrt',
            'body' => [
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->indices()->status($params);

Settings
========
Return a report with index's current settings.

Expects `index` name.


        $params = [
            'index' => 'testrt',
        ];
        $response = $client->indices()->status($params);

Truncate
========
Truncates an index. 

Expects `index` name.
 
        $params = [
            'index' => 'testrt'
         ];
        $response = $client->indices()->truncate($params);                                                  
