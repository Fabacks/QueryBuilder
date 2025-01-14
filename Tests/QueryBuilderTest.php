<?php

use Fabacks\QueryBuilder;

final class QueryBuilderTest extends \PHPUnit\Framework\TestCase {
    
    public function getBuilder(): QueryBuilder {
        return new QueryBuilder();
    }

    public function test_simpleQuery() {
        $q = $this->getBuilder()
            ->from("users", "u");

        $sql = "SELECT * FROM users AS u;";
        $this->assertEquals($sql, $q->toSQL());
    }

    public function test_select() {
        $q = $this->getBuilder()
            ->select("id", "name", "product")
            ->from("users");

        $sql = "SELECT id, name, product FROM users;";
        $this->assertEquals($sql, $q->toSQL());
    }

    public function test_select_multiple() {
        $q = $this->getBuilder()
            ->select("id")
            ->select("name")
            ->from("users")
            ->select('product');

        $sql = "SELECT id, name, product FROM users;";
        $this->assertEquals($sql, $q->toSQL());
    }

    public function test_select_array() {
        $q = $this->getBuilder()
            ->select(["id", "name", "product"])
            ->from("users");

        $sql = "SELECT id, name, product FROM users;";
        $this->assertEquals($sql, $q->toSQL());
    }

    public function test_select_clear() {
        $q = $this->getBuilder()
            ->select("name, firstname")
            ->selectClear()
            ->from("users", "u")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u;", $q);

        $q = $this->getBuilder()
            ->select("name, firstname")
            ->selectClear()
            ->select("name")
            ->from("users", "u")
            ->toSQL();

        $query = "SELECT name FROM users AS u;";
        $this->assertEquals($query, $q);
    }

    public function test_join() {
        $q = $this->getBuilder()
            ->select("user.name")
            ->from("users")
            ->join("INNER", "user_order", "order", "order.user", "user.id");

        $sql = "SELECT user.name FROM users INNER JOIN user_order AS order ON order.user = user.id;";
        $this->assertEquals($sql, $q->toSQL());
    }
    
    public function test_where() {
        $q = $this->getBuilder()
            ->from("users")
            ->where("id > 4")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users WHERE id > 4;", $q);
    }

    public function test_where_multiple() {
        $q = $this->getBuilder()
            ->from("users")
            ->where("id > 4")
            ->where("age < 15")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users WHERE id > 4 AND age < 15;", $q);
    }

    public function test_where_parametric() {
        $q = $this->getBuilder()
            ->from("users")
            ->where("id > :id")
            ->setParam("id", 3)
            ->toSQL();

        $this->assertEquals("SELECT * FROM users WHERE id > 3;", $q);
    }
    
    public function test_groupBy() {
        $q = $this->getBuilder()
            ->from("users")
            ->groupBy("nom")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users GROUP BY nom;", $q);
    }

    public function test_groupBy_multiple() {
        $q = $this->getBuilder()
            ->from("users")
            ->groupBy("nom")
            ->groupBy("prenom")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users GROUP BY nom, prenom;", $q);
    }

    public function test_orderBy() {
        $q = $this->getBuilder()
            ->from("users", "u")
            ->orderBy("id", "DESC")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users AS u ORDER BY id DESC;", $q);
    }

    public function test_orderBy_multiple() {
        $q = $this->getBuilder()
            ->from("users")
            ->orderBy("id", "ezaearz")
            ->orderBy("name", "desc")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users ORDER BY id, name DESC;", $q);
    }

    public function test_having() {
        $q = $this->getBuilder()
            ->from("users")
            ->having("age > 10")
            ->toSQL();

        $this->assertEquals("SELECT * FROM users HAVING age > 10;", $q);
    }

    public function test_having_multiple() {
        $q = $this->getBuilder()
            ->from("users")
            ->having("age > 10")
            ->having("brother = 2", "AND")
            ->toSQL();

        $query ="SELECT * FROM users HAVING age > 10 AND brother = 2;";
        $this->assertEquals($query, $q);
    }

    public function test_limit() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 10;";
        $this->assertEquals($query, $q);
    }

    public function test_limitOffset() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10, 5)
            ->orderBy("id", "DESC")
            ->toSQL();

        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 5;";
        $this->assertEquals($query, $q);
    }

    public function test_offset() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->offset(3)
            ->orderBy("id", "DESC")
            ->toSQL();

        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 3;";
        $this->assertEquals($query, $q);
    }

    public function test_page() {
        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->page(3)
            ->orderBy("id", "DESC")
            ->toSQL();

        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 20;";
        $this->assertEquals($query , $q);


        $q = $this->getBuilder()
            ->from("users")
            ->limit(10)
            ->page(1)
            ->orderBy("id", "DESC")
            ->toSQL();

        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 10 OFFSET 0;";
        $this->assertEquals($query, $q);
    }


}