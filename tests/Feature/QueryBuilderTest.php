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
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

class QueryBuilderTest extends TestCase
{

    function setUp(): void
    {
        parent::setUp();
        DB::table("products")->delete();
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


    function insertTableProduct(): void
    {
        $this->insertCategories();

        DB::table("products")->insert([
            "id" => "1",
            "name" => "iPhone 14 Pro Max",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);
        DB::table("products")->insert([
            "id" => "2",
            "name" => "Samsung Galaxy S21 Ultra",
            "category_id" => "SMARTPHONE",
            "price" => 18000000
        ]);
    }

    function testQueryBuilderJoin()
    {
        $this->insertTableProduct();

        $collection = DB::table("products")->join(
            "categories", "products.category_id", "=", "categories.id"
        )->select("products.id", "products.name"
            , "categories.name as category_name", "products.price as price")->get();

        self::assertCount(2, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }

    }

    function testQueryBuilderOrdering()
    {
        $this->insertTableProduct();

        $collection = DB::table("products")->
        orderBy("price", "desc")->
        orderBy("name", "asc")->
        get();

        self::assertCount(2, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }

    }

    function testQueryBuilderTakeSkip()
    {
        $this->insertCategories();

        $collection = DB::table("categories")->skip(2)->take(2)->get();

        self::assertCount(2, $collection);
        for ($i = 0; $i < count($collection); $i++) {
            Log::info(json_encode($collection[$i]));
        }
    }


    function testQueryBuilderChunkResult()
    {

        $this->insertTableProduct();

        DB::table("categories")
            ->orderBy("id")
            ->chunk(1, function ($collection) {
                self::assertNotNull($collection);

                foreach ($collection as $item) {
                    Log::info(json_encode($item));
                }
            });

    }

    function testQueryBuilderLazyResult()
    {
        $this->insertTableProduct();

        DB::table("categories")->orderBy("id")
            ->lazy(1)->each(function ($item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));
            });
    }

    function testQueryBuilderCursorResult()
    {
        $this->insertTableProduct();

        DB::table("categories")->orderBy("id")
            ->cursor()
            ->each(function ($item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));
            });
    }

    function testQueryBuilderAggregate()
    {
        $this->insertTableProduct();

        $result = DB::table("products")->count("id");
        self::assertEquals(2, $result);

        $result = DB::table("products")->min("price");
        self::assertEquals(18000000, $result);

        $result = DB::table("products")->max("price");
        self::assertEquals(20000000, $result);

        $result = DB::table("products")->avg("price");
        self::assertEquals(19000000, $result);

        $result = DB::table("products")->sum("price");
        self::assertEquals(38000000, $result);
    }

    function testQueryBuilderRAW()
    {
        $this->insertTableProduct();

        $collection = DB::table("products")
            ->select(
                DB::raw("count(*) as total_product"),
                DB::raw("min(price) as min_price"),
                DB::raw("max(price) as max_price"),
            )->get();

        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(18000000, $collection[0]->min_price);
        self::assertEquals(20000000, $collection[0]->max_price);
    }

    function insertProductFood()
    {
        DB::table("products")->insert(["id" => "3", "name" => "bakso", "category_id" => "FOOD", "price" => 20000]);
        DB::table("products")->insert(["id" => "4", "name" => "mie ayam", "category_id" => "FOOD", "price" => 15000]);
    }

    function testQueryBuilderGrouping()
    {
        $this->insertTableProduct();
        $this->insertProductFood();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->get();

        self::assertCount(2, $collection);
        self::assertEquals("SMARTPHONE", $collection[0]->category_id);
        self::assertEquals("FOOD", $collection[1]->category_id);
        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(2, $collection[1]->total_product);

    }

    function testQueryBuilderHaving()
    {
        $this->insertTableProduct();
        $this->insertProductFood();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->having(DB::raw("count(*)"), ">", "2")
            ->get();

        self::assertCount(0, $collection);
    }

    function testQueryBuilderPagination()
    {
        $this->insertCategories();

        $paginate = DB::table("categories")
            ->paginate(perPage: 2, page: 2);

        self::assertEquals(2, $paginate->currentPage());
        self::assertEquals(2, $paginate->perPage());
        self::assertEquals(2, $paginate->lastPage());
        self::assertEquals(4, $paginate->total());

        $collections = $paginate->items();

        self::assertCount(2, $collections);
        for ($i = 0; $i < count($collections); $i++) {
            Log::info(json_encode($collections[$i]));
        }


    }

    function testIterateAllPagination()
    {
        $this->insertCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table("categories")->paginate(perPage: 2, page: $page);
            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;
                $collection = $paginate->items();
                self::assertCount(2, $collection);
                foreach ($collection as $item) {
                    Log::info(json_encode($item));
                }
            }
        }
    }

    function testQueryBuilderCursorPaginate()
    {
        $this->insertCategories();

        $cursor = "id";

        while (true) {

            $paginate = DB::table("categories")->orderBy("id")->cursorPaginate(perPage: 2, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));

            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }

        }
    }

    function testSeeding()
    {
        $this->seed(CategorySeeder::class);

        $collection = DB::table("categories")->get();
        self::assertCount(4, $collection);

        for ($i = 0; $i < count($collection); $i++) {
            self::assertnotnull($collection[$i]);
            Log::info(json_encode($collection[$i]));
        }
    }

}
