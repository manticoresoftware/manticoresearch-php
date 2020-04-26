Cluster
-----

Nodes namespace contains methods for dealing with cluster operations. Available for  Manticore Search 3.4 or above.

Create
======
Create a new cluster.
`cluster` is mandatory as the name of the cluster.

`body` is optional and can contain `path` as alternative folder for storing the metadata of replication and `nodes` for list of servers that will join the cluster.

 
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
Update a cluster.
`cluster` is mandatory as the name of the cluster.

`body` parameters:
* `operation` -  mandatory, can be
      * add - add index to cluster
      * drop - drop index from cluster
      * update - trigger nodes in the cluster to update the rejoin nodes list in case of a cluster restart
* `index` - mandatory for add/drop, the index name that is added or dropped from the cluster


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
Delete a cluster.
`cluster` is mandatory as the name of the cluster.

        $params = [
            'cluster' => 'mycluster',
            'body' => [
                
            ]
        ];
        $response = $client->cluster()->delete($params);                
        
Join
====
Join to a cluster.
`cluster` is mandatory as the name of the cluster to join.
There are 2 syntaxes for `body` :
* simple version where  the address of one of the cluster's nodes is specified by `node`
* advanced version where alternative `path` for replication metadata must be set and the list of all nodes of the cluster must be set (by `nodes`)


        $params = [
            'cluster' => 'mycluster',
            'body' => [
                'node'
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
                    'value=>'`'
                 ]
                 
             ]
         ];
         $response = $client->cluster()->set($params);
  
