<?php

use JeffBeltran\Bob\TheBuilder;

class NoFiltersTest extends TestCase
{
    /** @test */
    public function it_returns_all_posts()
    {
        Factory(PostStub::class, 5)->create();

        $bob = new TheBuilder(PostStub::class);

        $this->assertCount(5, $bob->getResults());
    }

    /** @test */
    public function it_returns_single_resource()
    {
        $this->withoutExceptionHandling();

        $postOne = Factory(PostStub::class)->create();
        $postTwo = Factory(PostStub::class)->create();

        $queryFilter = new TheBuilder(PostStub::class, $postTwo->id);

        $results = $queryFilter->getResults();
        
        $this->assertEquals($postTwo->id, $results->id);
        $this->assertCount(2, PostStub::all());
    }
}