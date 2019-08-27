<?php

use Fabacks\QueryBuilder;

final class QueryBuilderTest extends \PHPUnit\Framework\TestCase {
    
    public function getBuilder(): QueryBuilder {
        return new QueryBuilder();
    }
    
    public function testSimpleQuery() {
        $q = $this->getBuilder()
            ->from("users", "u")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u", $q);
    }

    public function testOrderBy() {
        $q = $this->getBuilder()
            ->from("users", "u")
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u ORDER BY id DESC", $q);
    }

    public function testMultipleOrderBy() {
        $q = $this->getBuilder()
            ->from("users")
            ->orderBy("id", "ezaearz")
            ->orderBy("name", "desc")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id, name DESC", $q);
    }

    public function testLimit() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10", $q);
    }

    public function testOffset() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->offset(3)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 3", $q);
    }

    public function testPage() {
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

    public function testCondition() {
        $q = $this->getBuilder()
            ->from("users")
            ->where("id > :id")
            ->setParam("id", 3)
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users WHERE id > 3 ORDER BY id DESC LIMIT 10", $q);
    }

    public function testSelect() {
        $q = $this->getBuilder()
            ->select("id", "name", "product")
            ->from("users");
        $this->assertEquals("SELECT id, name, product FROM users", $q->toSQL());
    }

    public function testSelectMultiple() {
        $q = $this->getBuilder()
            ->select("id", "name")
            ->from("users")
            ->select('product');

        $this->assertEquals("SELECT id, name, product FROM users", $q->toSQL());
    }

    public function testSelectAsArray() {
        $q = $this->getBuilder()
            ->select(["id", "name", "product"])
            ->from("users");
            
        $this->assertEquals("SELECT id, name, product FROM users", $q->toSQL());
    }
}