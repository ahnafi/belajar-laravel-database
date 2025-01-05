<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    function testTransactionSuccess()
    {
        DB::transaction(function () {

            DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);

            DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                ["FRUIT", "fruit", "Fruit Category", "2000-01-01 00:00:00"]);

        });

        $result = DB::select("SELECT * FROM categories");
        self::assertCount(2, $result);
    }

    function testTransactionFailed()
    {
        try {
            DB::transaction(function () {

                DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                    ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);
                DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                    ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);

            });
        } catch (QueryException $exception) {
            echo $exception->getMessage();
        }

        $result = DB::select("SELECT * FROM categories");
        self::assertCount(0, $result);
    }

    function testTransactionManual()
    {
        try {
            DB::beginTransaction();

            DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);

            DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                ["FRUIT", "fruit", "Fruit Category", "2000-01-01 00:00:00"]);

            DB::commit();
        } catch (QueryException $exception) {
            echo $exception->getMessage();
            DB::rollBack();
        }

        $result = DB::select("SELECT * FROM categories");
        self::assertCount(2, $result);
    }

    function testTransactionManualFailed()
    {
        try {
            DB::beginTransaction();

            DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);

            DB::insert("INSERT INTO categories (id,name,description,created_at) VALUES (?,?,?,?)",
                ["GADGET", "gadget", "Gadget Category", "2000-01-01 00:00:00"]);

            DB::commit();
        } catch (QueryException $exception) {
            echo $exception->getMessage();
            DB::rollBack();
        }

        $result = DB::select("SELECT * FROM categories");
        self::assertCount(0, $result);
    }


}
