Cluster
-------

The Nodes namespace includes methods for handling cluster operations. These are available for Manticore Search 3.4 and higher.

Create
======
Create a new cluster.
The `cluster` parameter is required, as it specifies the name of the cluster.

The `body` parameter is optional and may contain `path` as an alternative folder for storing replication metadata and `nodes` for the list of servers that will join the cluster.


        $params = [
            'cluster' => 'mycluster',
            'body' => [
                'path' => '/var/data/click_query/',
                'nodes' => 'clicks_mirror1:9312,clicks_mirror2:9312,clicks_mirror3:9312',
                
            ]
        ];
        $response = $client->cluster()->create($params);
Alter
======
Update a cluster
----------------
The `cluster` parameter is required, as it specifies the name of the cluster.

`body` parameters:
* `operation` - mandatory, can be:
      * add - add an index to the cluster
      * drop - remove an index from the cluster
      * update - trigger nodes in the cluster to update the rejoin nodes list in the event of a cluster restart
* `index` - mandatory for add/drop operations, specifies the index name that is added or removed from the cluster


        $params = [
            'cluster' => 'mycluster',
            'body' => [
               'operation' => 'add',
               'index' => 'newindex'
                
            ]
        ];
        $response = $client->cluster()->alter($params);        
        
        $params = [
            'cluster' => 'mycluster',
            'body' => [
               'operation' => 'drop',
               'index' => 'newindex'
                
            ]
        ];
        $response = $client->cluster->alter($params);                
        $params = [
            'cluster' => 'mycluster',
            'body' => [
               'operation' => 'update',
               
            ]
        ];
        $response = $client->cluster()->alter($params);  
                  
Delete
======
Delete a cluster
----------------
The `cluster` parameter is required, as it specifies the name of the cluster.

        $params = [
            'cluster' => 'mycluster',
            'body' => [
                
            ]
        ];
        $response = $client->cluster()->delete($params);                
        
Join
====
Join a cluster
--------------
The `cluster` parameter is required, as it specifies the name of the cluster to join.
There are two syntax options for the `body` parameter:
* Simple version - specify the address of one of the cluster's nodes using the `node` parameter
* Advanced version - set an alternative `path` for replication metadata and provide the list of all nodes in the cluster using the `nodes` parameter


        $params = [
            'cluster' => 'mycluster',
            'body' => [
                'node' => 'address:port'
            ]
        ];
        $response = $client->cluster->join($params);
        
        $params = [
            'cluster' => 'mycluster',
            'body' => [
                'path' => '/var/data/click_query/',
                'nodes' => 'clicks_mirror1:9312;clicks_mirror2:9312;clicks_mirror3:9312',
                
            ]
        ];
        $response = $client->cluster()->join($params);
 
Set
===
Set a Galera option to the cluster.
 
         $params = [
             'cluster' => 'mycluster',
             'body' => [
                 'variable'=> [
                    'name' => 'pc.bootstrap',
                    'value'=>'1'
                 ]
                 
             ]
         ];
         $response = $client->cluster()->set($params);
  
<!-- proofread -->