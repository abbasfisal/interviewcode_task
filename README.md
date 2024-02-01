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

___

## seed database

``
php artisan migrate --seed
``

___

## Access to Swagger Documentation

``
http://127.0.0.1:8000/api/documentation
``
___

### :توجه داشته باشید که در فرایند ویرایش سفارش
<br>

```اگر محصولی که در سفارش ثبت شده مجدد ارسال نشود
آن محصول از لیست سفارش حذف شده و تعداد موجودی آن به انبار اضافه میشود
و اگر محصول جدید در ویرایش سفارش ارسال شود آن محصول به لیست محصولات آن سفارش اضافه میشود و موجودی از انبار کسر می گردد
چنانچه یک محصول در لیست محصولات یک سفارش موجود باشد و تعداد سفارش آن بیشتر از تعداد ثبت شده باشد تعداد اضافه شده از انبار کسر می گردد و چنانچه کمتر باشد به انبار اضافه می گردد
```
