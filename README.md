# Interview Coding

``
you must set replicaset for mongo db to support transaction
``

`` nano /et/mongod.conf``

#### add this line into it:

```.apacheconf
replication:
  replSetName: "rs0"

```

#### then in terminal

```
mongosh
----
rs.initiate();
```

#### then can check status by this command

``` rs.status()```

### then add replica option into your laravel database.php file

```` json
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 27017),
    'database' => env('DB_DATABASE', 'interviewdb'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'options'  => [
            'replicaSet' => 'rs0',
            'database' => 'admin'
           ],
]

``
