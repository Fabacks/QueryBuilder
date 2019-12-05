<?php

use Fabacks\QueryBuilder;

final class QueryBuilderTest extends \PHPUnit\Framework\TestCase {
    
    public function getBuilder(): QueryBuilder {
        return new QueryBuilder();
    }
    
    public function test_SimpleQuery() {
        $q = $this->getBuilder()
            ->from("users", "u")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u", $q);
    }

    public function test_SelectClear() {
        $q = $this->getBuilder()
            ->select("name, firstname")
            ->selectClear("name, firstname")
            ->from("users", "u")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u", $q);
    }
    

    public function test_OrderBy() {
        $q = $this->getBuilder()
            ->from("users", "u")
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u ORDER BY id DESC", $q);
    }

    public function test_MultipleOrderBy() {
        $q = $this->getBuilder()
            ->from("users")
            ->orderBy("id", "ezaearz")
            ->orderBy("name", "desc")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id, name DESC", $q);
    }

    public function test_Limit() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10", $q);
    }

    public function test_LimitOffset() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10, 5)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 5", $q);
    }

    public function test_Offset() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->offset(3)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 3", $q);
    }

    public function test_Page() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->page(3)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 20", $q);

        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->page(1)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 0", $q);
    }

    public function test_Condition() {
        $q = $this->getBuilder()
            ->from("users")
            ->where("id > :id")
            ->setParam("id", 3)
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users WHERE id > 3 ORDER BY id DESC LIMIT 10", $q);
    }

    public function test_Select() {
        $q = $this->getBuilder()
            ->select("id", "name", "product")
            ->from("users");
        $this->assertEquals("SELECT id, name, product FROM users", $q->toSQL());
    }

    public function test_SelectMultiple() {
        $q = $this->getBuilder()
            ->select("id", "name")
            ->from("users")
            ->select('product');

        $this->assertEquals("SELECT id, name, product FROM users", $q->toSQL());
    }

    public function test_SelectAsArray() {
        $q = $this->getBuilder()
            ->select(["id", "name", "product"])
            ->from("users");
            
        $this->assertEquals("SELECT id, name, product FROM users", $q->toSQL());
    }

    public function test_Join() {
        $q = $this->getBuilder()
            ->select("user.name")
            ->from("users")
            ->join("INNER", "user_order", "order", "order.user", "user.id");
        
        $sql = "SELECT user.name FROM users INNER JOIN user_order AS order ON order.user = user.id";
        $this->assertEquals($sql, $q->toSQL());
    }
}