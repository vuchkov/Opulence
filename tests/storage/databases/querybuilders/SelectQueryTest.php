<?php
/**
 * Copyright (C) 2014 David Young
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 *
 * Tests the select query
 */
namespace RamODev\Databases\RDBMS\QueryBuilders;

require_once(__DIR__ . "/../../../../databases/rdbms/postgresql/querybuilders/SelectQuery.php");

class SelectQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests adding a "GROUP BY" statement to one that was already started
     */
    public function testAddingGroupBy()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users")
            ->groupBy("id")
            ->addGroupBy("name");
        $this->assertEquals("SELECT id, name FROM users GROUP BY id, name", $query->getSQL());
    }

    /**
     * Tests adding an "AND"ed and an "OR"ed "WHERE" clause
     */
    public function testAddingOrWhereAndWhere()
    {
        $query = new SelectQuery("id");
        $query->from("users")
            ->where("id > 10")
            ->orWhere("name <> 'dave'")
            ->andWhere("name <> 'brian'");
        $this->assertEquals("SELECT id FROM users WHERE (id > 10) OR (name <> 'dave') AND (name <> 'brian')", $query->getSQL());
    }

    /**
     * Tests adding an "ORDER BY" statement to one that was already started
     */
    public function testAddingOrderBy()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users")
            ->orderBy("id ASC")
            ->addOrderBy("name DESC");
        $this->assertEquals("SELECT id, name FROM users ORDER BY id ASC, name DESC", $query->getSQL());
    }

    public function testAddingSelectExpression()
    {
        $query = new SelectQuery("id");
        $query->from("users")
            ->addSelectExpression("name");
        $this->assertEquals("SELECT id, name FROM users", $query->getSQL());
    }

    /**
     * Tests adding a "HAVING" condition that will be "AND"ed
     */
    public function testAndHaving()
    {
        $query = new SelectQuery("name");
        $query->from("users")
            ->groupBy("name")
            ->having("COUNT(name) > 1")
            ->andHaving("COUNT(name) < 5");
        $this->assertEquals("SELECT name FROM users GROUP BY name HAVING (COUNT(name) > 1) AND (COUNT(name) < 5)", $query->getSQL());
    }

    /**
     * Tests adding a "WHERE" condition that will be "AND"ed
     */
    public function testAndWhere()
    {
        $query = new SelectQuery("id");
        $query->from("users")
            ->where("id > 10")
            ->andWhere("name <> 'dave'");
        $this->assertEquals("SELECT id FROM users WHERE (id > 10) AND (name <> 'dave')", $query->getSQL());
    }

    /**
     * Tests a basic query
     */
    public function testBasicQuery()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users");
        $this->assertEquals("SELECT id, name FROM users", $query->getSQL());
    }

    /**
     * Tests a basic query with a table alias
     */
    public function testBasicQueryWithAlias()
    {
        $query = new SelectQuery("u.id", "u.name");
        $query->from("users", "u");
        $this->assertEquals("SELECT u.id, u.name FROM users AS u", $query->getSQL());
    }

    /**
     * Tests all our methods in a single, complicated query
     */
    public function testEverything()
    {
        $query = new SelectQuery("u.id", "u.name", "e.email");
        $query->addSelectExpression("p.password")
            ->from("users", "u")
            ->innerJoin("log", "l", "l.userid = u.id")
            ->leftJoin("emails", "e", "e.userid = u.id")
            ->rightJoin("password", "p", "p.userid = u.id")
            ->where("u.id <> 10", "u.name <> :notAllowedName")
            ->addNamedPlaceholderValue("notAllowedName", "dave")
            ->andWhere("u.id <> 9")
            ->orWhere("u.name = :allowedName")
            ->addNamedPlaceholderValue("allowedName", "brian")
            ->groupBy("u.id", "u.name", "e.email")
            ->addGroupBy("p.password")
            ->having("count(*) > 1")
            ->andHaving("count(*) < 5")
            ->orHaving("count(*) = 2")
            ->orderBy("u.id DESC")
            ->addOrderBy("u.name ASC")
            ->limit(2)
            ->offset(1);
        $this->assertEquals("SELECT u.id, u.name, e.email, p.password FROM users AS u INNER JOIN log AS l ON l.userid = u.id LEFT JOIN emails AS e ON e.userid = u.id RIGHT JOIN password AS p ON p.userid = u.id WHERE (u.id <> 10) AND (u.name <> :notAllowedName) AND (u.id <> 9) OR (u.name = :allowedName) GROUP BY u.id, u.name, e.email, p.password HAVING (count(*) > 1) AND (count(*) < 5) OR (count(*) = 2) ORDER BY u.id DESC, u.name ASC LIMIT 2 OFFSET 1", $query->getSQL());
    }

    /**
     * Tests adding a "GROUP BY" statement
     */
    public function testGroupBy()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users")
            ->groupBy("id", "name");
        $this->assertEquals("SELECT id, name FROM users GROUP BY id, name", $query->getSQL());
    }

    /**
     * Tests adding a "HAVING" condition
     */
    public function testHaving()
    {
        $query = new SelectQuery("name");
        $query->from("users")
            ->groupBy("name")
            ->having("COUNT(name) > 1");
        $this->assertEquals("SELECT name FROM users GROUP BY name HAVING (COUNT(name) > 1)", $query->getSQL());
    }

    /**
     * Tests adding an "INNER JOIN" statement
     */
    public function testInnerJoin()
    {
        $query = new SelectQuery("id");
        $query->from("users", "u")
            ->innerJoin("log", "l", "l.userid = u.id");
        $this->assertEquals("SELECT id FROM users AS u INNER JOIN log AS l ON l.userid = u.id", $query->getSQL());
    }

    /**
     * Tests adding a "JOIN" statement
     */
    public function testJoin()
    {
        $query = new SelectQuery("u.id");
        $query->from("users", "u")
            ->join("log", "l", "l.userid = u.id");
        $this->assertEquals("SELECT u.id FROM users AS u INNER JOIN log AS l ON l.userid = u.id", $query->getSQL());
    }

    /**
     * Tests adding an "LEFT JOIN" statement
     */
    public function testLeftJoin()
    {
        $query = new SelectQuery("id");
        $query->from("users", "u")
            ->leftJoin("log", "l", "l.userid = u.id");
        $this->assertEquals("SELECT id FROM users AS u LEFT JOIN log AS l ON l.userid = u.id", $query->getSQL());
    }

    /**
     * Tests adding a "LIMIT" statement
     */
    public function testLimit()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users")
            ->limit(5);
        $this->assertEquals("SELECT id, name FROM users LIMIT 5", $query->getSQL());
    }

    /**
     * Tests adding multiple "JOIN" statements
     */
    public function testMultipleJoins()
    {
        $query = new SelectQuery("id");
        $query->from("users", "u")
            ->join("log", "l", "l.userid = u.id")
            ->join("emails", "e", "e.userid = u.id");
        $this->assertEquals("SELECT id FROM users AS u INNER JOIN log AS l ON l.userid = u.id INNER JOIN emails AS e ON e.userid = u.id", $query->getSQL());
    }

    /**
     * Tests adding a "OFFSET" statement
     */
    public function testOffset()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users")
            ->offset(5);
        $this->assertEquals("SELECT id, name FROM users OFFSET 5", $query->getSQL());
    }

    /**
     * Tests adding a "HAVING" condition that will be "OR"ed
     */
    public function testOrHaving()
    {
        $query = new SelectQuery("name");
        $query->from("users")
            ->groupBy("name")
            ->having("COUNT(name) > 1")
            ->orHaving("COUNT(name) < 5");
        $this->assertEquals("SELECT name FROM users GROUP BY name HAVING (COUNT(name) > 1) OR (COUNT(name) < 5)", $query->getSQL());
    }

    /**
     * Tests adding a "WHERE" condition that will be "OR"ed
     */
    public function testOrWhere()
    {
        $query = new SelectQuery("id");
        $query->from("users")
            ->where("id > 10")
            ->orWhere("name <> 'dave'");
        $this->assertEquals("SELECT id FROM users WHERE (id > 10) OR (name <> 'dave')", $query->getSQL());
    }

    /**
     * Tests adding an "ORDER BY" statement
     */
    public function testOrderBy()
    {
        $query = new SelectQuery("id", "name");
        $query->from("users")
            ->orderBy("id ASC", "name DESC");
        $this->assertEquals("SELECT id, name FROM users ORDER BY id ASC, name DESC", $query->getSQL());
    }

    /**
     * Tests adding an "RIGHT JOIN" statement
     */
    public function testRightJoin()
    {
        $query = new SelectQuery("id");
        $query->from("users", "u")
            ->rightJoin("log", "l", "l.userid = u.id");
        $this->assertEquals("SELECT id FROM users AS u RIGHT JOIN log AS l ON l.userid = u.id", $query->getSQL());
    }

    /**
     * Tests setting a "HAVING" condition, then resetting it
     */
    public function testSettingHavingConditionWhenItWasAlreadySet()
    {
        $query = new SelectQuery("name");
        $query->from("users")
            ->groupBy("name")
            ->having("COUNT(name) > 1")
            ->having("COUNT(name) < 5");
        $this->assertEquals("SELECT name FROM users GROUP BY name HAVING (COUNT(name) < 5)", $query->getSQL());
    }

    /**
     * Tests setting a "WHERE" condition, then resetting it
     */
    public function testSettingWhereConditionWhenItWasAlreadySet()
    {
        $query = new SelectQuery("name");
        $query->from("users")
            ->where("id = 1")
            ->where("id = 2");
        $this->assertEquals("SELECT name FROM users WHERE (id = 2)", $query->getSQL());
    }

    /**
     * Tests adding a "WHERE" condition
     */
    public function testWhere()
    {
        $query = new SelectQuery("id");
        $query->from("users")
            ->where("id > 10", "name <> 'dave'");
        $this->assertEquals("SELECT id FROM users WHERE (id > 10) AND (name <> 'dave')", $query->getSQL());
    }
} 