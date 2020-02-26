Cluster
-----

Nodes namespace contains method for dealing with cluster operations.

Create
======
Create a new cluster.

        $params = [
            'cluster' => 'mycluster',
            'body' => [
                'path' => '',
                'nodes' => '',
                
            ]
        ];
        $response = $client->cluster->create($params);
Alter
======
Update a cluster

        $params = [
            'cluster' => 'mycluster',
            'body' => [
               'operation' => 'add',
               'index' => 'newindex'
                
            ]
        ];
        $response = $client->cluster->alter($params);        
        
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
        $response = $client->cluster->alter($params);  
                  
Create
======
Delete a cluster

        $params = [
            'cluster' => 'mycluster',
            'body' => [
                
            ]
        ];
        $response = $client->cluster->delete($params);                
        
Join
====
Join to a cluster.

        $params = [
            'cluster' => 'mycluster',
            'body' => [
                'path' => '',
                'nodes' => '',
                
            ]
        ];
        $response = $client->cluster->join($params);
 
 Set
 ===
Set a Galera option to the cluster
 
         $params = [
             'cluster' => 'mycluster',
             'body' => [
                 'variable'=> [
                    'name' => '',
                    'value=>''
                 ]
                 
             ]
         ];
         $response = $client->cluster->set($params);
  