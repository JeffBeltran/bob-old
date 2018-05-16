<?php

use JeffBeltran\Bob\TheBuilder;

class FiltersTest extends TestCase
{

    private function getQueryStringResults($filters = [])
    {
        $request = request();
        $request->merge($filters);

        $bob = new TheBuilder(PostStub::class);
        return $bob->getResults();
    }

    /** @test */
    function it_sorts_by_title()
    {
        $this->withoutExceptionHandling();

        $postOne = factory(PostStub::class)->create([
            'title' => 'Alpha'
        ]);
        $postTwo = factory(PostStub::class)->create([
            'title' => 'Zulu'
        ]);
        $postThree = factory(PostStub::class)->create([
            'title' => 'Hotel'
        ]);
        $this->assertCount(3, PostStub::all());

        // setting query string values this is the same as /endpoint?sort=title,asc
        $returnedData = $this->getQueryStringResults([
            'sort' => 'title,asc'
        ]);

        $this->assertCount(3, $returnedData);
        $this->assertEquals($postOne->title, $returnedData->first()->title);
        $this->assertEquals($postTwo->title, $returnedData->last()->title);
    }
}