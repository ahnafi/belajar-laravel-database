<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();
        DB::delete("delete from categories");
    }

    function test_example()
    {
        DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
            ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);

        $result = DB::select("SELECT id,name,description,created_at FROM categories WHERE id= ? ", ["GADGET"]);

        self::assertCount(1, $result);
        self::assertEquals("GADGET", $result[0]->id);
        self::assertEquals("gadget", $result[0]->name);
        self::assertEquals("Gadget Category", $result[0]->description);
        self::assertEquals("2000-01-01 00:00:00", $result[0]->created_at);
    }

    function testNamedBinding()
    {
        DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (:id,:name,:description,:created_at)",
            [
                "id" => "GADGET",
                "name" => "gadget",
                "description" => "Gadget Category",
                "created_at" => "2000-01-01 00:00:00"
            ]);

        $result = DB::select("SELECT id,name,description,created_at FROM categories WHERE id= ? ", ["GADGET"]);

        self::assertCount(1, $result);
        self::assertEquals("GADGET", $result[0]->id);
        self::assertEquals("gadget", $result[0]->name);
        self::assertEquals("Gadget Category", $result[0]->description);
        self::assertEquals("2000-01-01 00:00:00", $result[0]->created_at);
    }
}
