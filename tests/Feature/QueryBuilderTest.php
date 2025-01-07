<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use function Laravel\Prompts\table;

class QueryBuilderTest extends TestCase
{

    function setUp(): void
    {
        parent::setUp();
        DB::delete("delete from categories");
//        DB::delete("delete from counters");
    }

    function testQueryBuilderInsert()
    {
        DB::table('categories')->insert([
            "id" => "GADGET",
            "name" => "Gadget",
        ]);

//        untuk auto increment bagus
        $id = DB::table('categories')->insertGetId([
            "id" => "FOOD",
            "name" => "Food",
        ]);

        var_dump($id);

        $res = DB::select("select * from categories");
        $this->assertCount(2, $res);
    }

    function testQueryBuilderInsertFailed()
    {
        try {
            DB::table('categories')->insert([
                "id" => "GADGET",
                "name" => "Gadget",
            ]);

            DB::table('categories')->insertOrIgnore([
                "id" => "GADGET",
                "name" => "Gadget",
            ]);

        } catch (QueryException $exception) {
            echo $exception->getMessage();
        }
        $res = DB::select("select * from categories");
        $this->assertCount(1, $res);
    }

    function testQueryBuilderSelect()
    {
        $this->testQueryBuilderInsert();

        $collection = DB::table('categories')->select('id', 'name')->get();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    function insertCategories(): void
    {
        $this->seed(CategorySeeder::class);
    }

    function testQueryBuilderWhere()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->orWhere(function (Builder $builder) {
            $builder->where("id", "=", "SMARTPHONE");
            $builder->orwhere("id", "=", "FOOD");
        })->get();

        self::assertCount(2, $collection);

        foreach ($collection as $item) {
            Log::info(json_encode($item));
        }
    }

    function testQueryBuilderWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->whereBetween("created_at", ["2020-10-10 00:00:00", "2020-10-10 23:59:59"])->get();

        self::assertCount(4, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }
    }

    function testQueryBuilderWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->whereIn("id", ["SMARTPHONE", "LAPTOP"])->get();

        self::assertCount(2, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }
    }

    function testQueryBuilderWhereNull()
    {
        $this->insertCategories();
        $collection = DB::table("categories")->whereNull("description")->get();

        self::assertCount(4, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }

    }

    function testQueryBuilderWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->whereDate("created_at", "2020-10-10")->get();

        self::assertCount(4, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }
    }

    function testQueryBuilderUpdate()
    {
        $this->insertCategories();

        DB::table("categories")->where("id", "=", "SMARTPHONE")->update([
            "name" => "handphone"
        ]);

        $collection = DB::table("categories")->where("id", "=", "SMARTPHONE")->get();

        self::assertEquals("handphone", $collection[0]->name);
        self::assertNotSame("SMARTPHONE", $collection[0]->name);

        self::assertCount(1, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }
    }

    function testQueryBuilderUpdateOrInsert()
    {
        $this->insertCategories();

        DB::table("categories")->updateOrInsert([
            "id" => "VOUCHER",
        ], [
            "name" => "VOUCHER",
            "description" => "VOUCHER",
            "created_at" => "2025-01-01 00:00:00",
        ]);

        $collection = DB::table("categories")->where("id", "=", "VOUCHER")->get();

        self::assertEquals("VOUCHER", $collection[0]->name);
        self::assertCount(1, $collection);

        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }

    }

    function testQueryBuilderUpdateIncrement()
    {
        DB::table("counters")->where("id", "=", "sample")->increment("counter", 1);

        $collection = DB::table("counters")->where("id", "=", "sample")->get();

//        self::assertEquals(1, $collection[0]->counter);
        self::assertCount(1, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }

    }

    function testQueryBuilderDelete()
    {
        $this->insertCategories();

        DB::table("categories")->where("id", "=", "SMARTPHONE")->delete();
        $collection = DB::table("categories")->where("id", "=", "SMARTPHONE")->get();

        self::assertCount(0, $collection);
    }

}
