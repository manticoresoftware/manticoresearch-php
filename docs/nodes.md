Nodes
-----

Nodes namespace contains method for dealing with daemon operations.

AgentStatus
===========
Prints information about a specific remote agent or of all remote agents.

        $params = [
            'body' => [
                'agent' => '141.212.121.211:9312:index'
                'pattern' => 'propertyname'
            ]
        ];
        $response = $client->nodes->agentstatus($params);            

CreateFunction
==============

Register an UDF.

        $params = [
            'body' => [
                'name => ''
                'type' => ''
                'library' => 'udf.so'
            ]
        ];
        $response = $client->nodes->createfunction($params);  
        
CreatePlugin
============

Register a plugin.

        $params = [
            'body' => [
                'name => ''
                'type' => ''
                'library' => 'udf.so'
            ]
        ];
        $response = $client->nodes->createplugin($params);                 
        
Debug
=====

A command that can run some debug commands.

        $params = [
            'body' => [
                'subcommand' => ''
            ]
        ];
        $response = $client->nodes->createplugin($params);                  
        
DropFunction
============

De-register an UDF

        $params = [
            'body' => [
                'name => ''
            ]
        ];
        $response = $client->nodes->dropfunction($params);                 
        
DropPlugin
===========

De-register a plugin

        $params = [
            'body' => [
                'name => ''
            ]
        ];
        $response = $client->nodes->dropplugin($params);                 

FlushAttributes
================

Flush attributes to disk

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes->flushattributes($params);
FlushHostnames
================

Flush hostnames cache.

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes->flushhostnames($params);                    
FlushLogs
========

Flush logs

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes->flushlogs($params);                                                                         
Plugins
========

Return list of loaded plugins and functions

        $params = [
            'body' => [

            ]
        ];
        $response = $client->nodes->plugins($params);                     
ReloadPlugins
=============

Reloads plugins from a library

        $params = [
            'body' => [
                'library' => ''
            ]
        ];
        $response = $client->nodes->reloadplugins($params);               
Set
===

Set a server variable

        $params = [
            'body' => [
                'variable' => [
                    'name' => 'query_log_format',
                    'value => 'sphinxql'
                ]
            ]
        ];
        $response = $client->nodes->set($params);      
        
Status
======
Returns information about the current daemon.

        $params = [
            'body' => [
                'pattern' => 'uptime'
            ]
        ];
        $response = $client->nodes->status($params);           
Tables
======
Return list of current indexes

        $params = [
            'body' => [
                'pattern' => 'rt'
            ]
        ];
        $response = $client->nodes->tables($params);                 
        
Threads
======
Return current running threads.

        $params = [
            'body' => [
                'columns' => '50',
                'format' => 'sphinxql'
            ]
        ];
        $response = $client->nodes->tables($params);                    
Variables
=========
Return list of server variables

        $params = [
            'body' => [
                'variable_name' => 'character_set_client'
            ]
        ];
        $response = $client->nodes->tables($params);                                                                                                                            