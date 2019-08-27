<?php

$path = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR;
require $path."QueryBuilder.php";

$builder = new Database\QueryBuilder();
$q = $builder->from("users")
            ->where("id > :id")
            ->setParam("id", 3)
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

var_dump($q);