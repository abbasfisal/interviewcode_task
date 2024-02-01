# Interview Coding

### you must set replicaset for mongo db to support transaction

## set replica steps

### step 1

go to :`` nano /et/mongod.conf``

### step 2

#### add this line into it:

```.apacheconf
replication:
  replSetName: "rs0"
```

### step 3

#### then in terminal

```
mongosh
```

```
rs.initiate();
```

#### then can check status by this command (must be active)

``` rs.status()```

### step 4

#### then add replica option into your laravel database.php file

```` 
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
````
