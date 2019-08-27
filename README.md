# Déscription

QueryBuilder est une librairie de création de requête SQL

## Installation

## With composer 
```php
    composer require fabacks/queryBuilder @dev
```

### Without composer
```php
    require_once YOUR_PATH."QueryBuilder.php";
```

## Usage
```php
$builder = new Fabacks\QueryBuilder();
$q = $builder->from("users")
            ->where("id > :id")
            ->setParam("id", 3)
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();
```


## License
[MIT](https://choosealicense.com/licenses/mit/)