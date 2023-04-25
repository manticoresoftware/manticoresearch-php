Nodes
-----

Nodes namespace contains methods for handling daemon operations or obtaining information about the current node.

AgentStatus
===========
Displays information about a specific remote agent or all remote agents.

`body` is optional. If the body is not provided, it will return information on all defined agents.
Results can be filtered by requesting information for a single agent - set by `agent`, or filter by an agent property using `pattern`.

        $params = [
            'body' => [
                'agent' => '141.212.121.211:9312:index'
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->nodes()->agentstatus($params);            

CreateFunction
==============

Register a UDF.

`body` parameters:

* `name` - name of the function
* `type` - the return type of the function (INT | INTEGER | BIGINT | FLOAT | STRING)
* `library` - name of the library file

        $params = [
            'body' => [
                'name => 'myudf'
                'type' => 'FLOAT'
                'library' => 'udf.so'
            ]
        ];
        $response = $client->nodes()->createfunction($params);  
        
CreatePlugin
============

Register a plugin.

`body` parameters:

* `name` - name of the plugin
* `type` - can be 'ranker', 'index_token_filter', 'query_token_filter'
* `library` - name of the library file

        $params = [
            'body' => [
                'name => 'myranker'
                'type' => 'ranker'
                'library' => 'myplugins.so'
            ]
        ];
        $response = $client->nodes()->createplugin($params);                 
        
Debug
=====

A command that can run some debug commands.
`body` has one parameter: the `subcommand` it needs to run.

        $params = [
            'body' => [
                'subcommand' => 'flush logs'
            ]
        ];
        $response = $client->nodes()->createplugin($params);                  
        
DropFunction
============

Deregister an UDF

        $params = [
            'body' => [
                'name => ''myufg
            ]
        ];
        $response = $client->nodes()->dropfunction($params);                 
        
DropPlugin
===========

Deregister a plugin

        $params = [
            'body' => [
                'name => 'myranker'
            ]
        ];
        $response = $client->nodes()->dropplugin($params);                 

FlushAttributes
================

Flush attributes to disk

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes()->flushattributes($params);
FlushHostnames
================

Flush hostnames cache.

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes()->flushhostnames($params);                    
FlushLogs
========

Flush logs.

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes()->flushlogs($params);                                                                         
Plugins
========

Return list of loaded plugins and functions.

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes()->plugins($params);                     
ReloadPlugins
=============

Reloads plugins from a library.

        $params = [
            'body' => [
                'library' => 'mylibrary.so'
            ]
        ];
        $response = $client->nodes()->reloadplugins($params);               
Set
===

Set a server variable.
`body` requires `variable` parameter which is an array containing the `name` of the value and the `value` to be set.

        $params = [
            'body' => [
                'variable' => [
                    'name' => 'query_log_format',
                    'value => 'sphinxql'
                ]
            ]
        ];
        $response = $client->nodes()->set($params);      
        
Status
======
Returns information and performance metrics about the current node.

If the node is part of a cluster, it will also provide information about the cluster.

Result can be filtered by setting `pattern` (on status metric names) parameter of `body`.

        $params = [
            'body' => [
                'pattern' => 'uptime'
            ]
        ];
        $response = $client->nodes()->status($params);           
Tables
======
Return list of current indexes.

Result can be filtered by setting `pattern` (on index names) parameter of `body`.

        $params = [
            'body' => [
                'pattern' => 'rt'
            ]
        ];
        $response = $client->nodes()->tables($params);                 
        
Threads
======
Return current running threads.
Optional `body` can contain formatting of the result by setting `columns` (number of chars in 'Info' panel) and `format` (with possible value 'sphinxql' to force sphinxql format of the commands)

        $params = [
            'body' => [
                'columns' => '50',
                'format' => 'sphinxql'
            ]
        ];
        $response = $client->nodes()->threads($params);                    
Variables
=========

Return list of server variables

Optionally, it can return the value of a single server variable by specifying the `variable_name` parameter in `body`.
 

        $params = [
            'body' => [
                'variable_name' => 'character_set_client'
            ]
        ];
        $response = $client->nodes()->variables($params);                                                                                                                            
<!-- proofread -->