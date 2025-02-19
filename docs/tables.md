Tables
-------

The Nodes namespace contains methods for operations made on Tables or information about them. Available for Manticore Search 3.4 or above.

Create
======
Create a new table.

`table` is mandatory.
`body` can contain:

* `columns` - definition of fields for tables with data
* `settings` - various table settings
* `silent` - optional, if set to true, the create will not fail with an error if there is already a table with the designated name

`body` requires the presence of a `columns` array for RT and PQ tables where keys are the field names.

Each field is an array that must contain a `type` definition.
`text` type also supports `options`, with current possible values being `indexed` and `stored`.

Table settings can be set in the `settings` parameter. Some settings can have multiple entries, like `local` for distributed 
tables. In this case, the value of the setting will be an array of values (see the distributed table example below).

By default, the table type is `Real-Time`. For PQ or distributed tables, the options must contain a `type` property.


        $params = [
            'table' => 'testrt',
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
        $response = $client->tables()->create($params);
        
For distributed tables, the body must contain only the `options` array, as they don't have any data (so no `columns`).

        $params = [
            'table' => 'mydistributed',
            'body' => [
                'options' => [
                    'type' => 'distributed',
                    'local' => [
                        'table1'
                        'table2`
                    ]
                ]
            ]
        ];
        $response = $client->tables()->create($params);        

       
Drop
===
Drop an existing table. `table` is mandatory.

`body` can contain the optional parameter `silent` - for not failing with an error in case the table doesn't exist.


        $params = [
            'table' => 'testrt',
            ['body' => ['silent'=>true ]]
         ];
        $response = $client->tables()->drop($params);
        
Alter
====
Alter performs changes on tables. Currently, only adding/dropping columns are supported.
Note that the `text` type cannot be used in this call.

Expects the table name.

The `body` parameters:
 
* `operation` - mandatory, possible values: add, drop
* `column` - for add, drop operations, the column is an array of
    * `name` - column name
    * `type` - data type
 

        $params = [
            'table' => 'testrt',
            'body' => [
                'operation' => 'add',
                'column' => [
                    'name' => 'tag',
                    'type'=> 'string'
                ]
                   
            ]
        ];
        $response = $client->tables()->alter($params);
        
        $params = [
            'table' => 'testrt',
            'body' => [
                'operation' => 'drop',
                'column' => [
                    'name' => 'tag'
                ]
                   
            ]
        ];
        $response = $client->tables()->alter($params);        

Describe
========
Returns the structure of a table.

Expects the table name.

`body` is optional. It supports `pattern` as a column name for filtering the structure result.

        $params = [
            'table' => 'testrt',
            'body' => [
                'pattern' => 'columnname'
            ]
        ];
        $response = $client->tables()->describe($params);

FlushRamchunk
=============
Flushes RAM chunk for an RT table.

Expects the table name.

        $params = [
            'table' => 'testrt',
         ];
        $response = $client->tables()->flushramchunk($params);               

FlushRttable
============
Flushes the RT table to disk.
Expects only the table name.

        $params = [
            'table' => 'testrt',
         ];
        $response = $client->tables()->flushrttable($params);

Optimize
========

Launches optimization on the RT table. By default, the command doesn't wait for the optimize operation to finish.

Expects the table name.

`body` is optional. Supports the `sync` parameter - if set, the command waits for the optimization to finish.


        $params = [
            'table' => 'testrt',
            'body' => [ 'sync'=>true]
         ];
        $response = $client->tables()->optimize($params);      

Status
======
Returns statistics about the table: documents, size, chunks, as well as query statistics.

Expects the table name.

`body` is optional. It supports `pattern` as a property/performance metric to filter upon.

        $params = [
            'table' => 'testrt',
            'body' => [
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->tables()->status($params);

Settings
========
Returns a report with the table's current settings.

Expects the table name.

        $params = [
            'table' => 'testrt',
        ];
        $response = $client->tables()->status($params);

Truncate
========
Truncates a table.

Expects the table name.
 
        $params = [
            'table' => 'testrt'
         ];
        $response = $client->tables()->truncate($params);                                                  
<!-- proofread -->