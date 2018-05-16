<?php

use JeffBeltran\Bob\TheBuilder;

class ReservedFiltersTest extends TestCase
{
    private function getQueryStringResults($filters = [])
    {
        $request = request();
        $request->merge($filters);

        $bob = new TheBuilder(PostStub::class);
        return $bob->getResults();
    }

    /** @test */
    public function it_gets_relationships()
    {
        $this->withoutExceptionHandling();

        $post = factory(PostStub::class)->create();
        factory(CommentStub::class, 5)->create([
            'post_stub_id' => $post->id
        ]);
    
        // setting query string values this is the same as /endpoint?with=comments
        $returnedData = $this->getQueryStringResults([
            'with' => 'comments'
        ]);

        $this->assertCount(1, $returnedData->toArray());
        $this->assertCount(5, $returnedData->toArray()[0]['comments']);
    }

    /** @test */
    public function it_returns_lenghtAwarePaginator_results()
    {
        $this->withoutExceptionHandling();

        factory(PostStub::class, 20)->create();

        // setting query string values this is the same as /endpoint?limit=5
        $returnedData = $this->getQueryStringResults([
            'limit' => '5'
        ]);

        $this->assertInstanceOf(Illuminate\Pagination\LengthAwarePaginator::class, $returnedData);
        $this->assertCount(5, $returnedData->items());
        $this->assertEquals(20, $returnedData->total());
    }

    /** @test */
    public function it_gets_paginated_results_with_relationships()
    {
        $this->withoutExceptionHandling();

        factory(CommentStub::class, 20)->create([
            'post_stub_id' => function () {
                return factory(PostStub::class)->create()->id;
            }
        ]);
        $this->assertCount(20, PostStub::all());
        $this->assertCount(20, CommentStub::all());
        
        // setting query string values this is the same as /endpoint?limit=5&with=comments
        $returnedData = $this->getQueryStringResults([
            'limit' => '5',
            'with' => 'comments'
        ]);

        $this->assertInstanceOf(Illuminate\Pagination\LengthAwarePaginator::class, $returnedData);
        $this->assertCount(5, $returnedData->items());
        $this->assertEquals(20, $returnedData->total());
            // check that each item has comment relationship
        foreach ($returnedData->items() as $item) {
            $this->assertCount(1, $item->comments);
        }
    }
}