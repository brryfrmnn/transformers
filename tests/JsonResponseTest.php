<?php

namespace Brryfrmnn\Transformers\Tests;

use PHPUnit\Framework\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Response;
use Brryfrmnn\Transformers\Json;
use Illuminate\Container\Container;

class JsonResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Facade::setFacadeApplication(new Container());
        Response::swap(new class {
            public function json($value, $status = 200, array $headers = [], $options = 0)
            {
                return $value;
            }
        });
    }

    public function test_paginator_response_structure()
    {
        $items = collect([
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
        ]);

        $paginator = new LengthAwarePaginator(
            $items->forPage(1, 2)->values(),
            $items->count(),
            2,
            1,
            ['path' => '/test']
        );

        $response = Json::response($paginator);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('links', $response);
        $meta = $response['meta'];

        $this->assertTrue($meta['status']);
        $this->assertEquals($items->count(), $meta['total']);
        $this->assertEquals(2, $meta['per_page']);
        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(2, $meta['last_page']);
        $this->assertEquals(1, $meta['from']);
        $this->assertEquals(2, $meta['to']);
    }
}
