# Neighbourhood Dashboard Form API

[Neighbourhood Dashboard](http://nd.solveninja.org/)

## Form Links
* [Refer a Needy & Needy Persons Supplies](https://ee.kobotoolbox.org/x/#Rpxexbz7)
* [Social Distance & Essential Supplies Marker](https://ee.kobotoolbox.org/x/#4v5Ilf7D)

## Setup

##### Change Database Name and Credentials on
```api/config/database.php```

##### Set the Data here.
```php
class Database{ 
    // specify your own database credentials
    private $host     = 'localhost';
    private $db_name  = 'db_name';
    private $username = 'db_user';
    private $password = 'db_pass';
    public $conn;
    ...
```