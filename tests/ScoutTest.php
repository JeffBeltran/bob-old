<?php

use JeffBeltran\Bob\TheBuilder;

class ScoutTest extends TestCase
{
    private function getQueryStringResults($filters = [])
    {
        $request = request();
        $request->merge($filters);

        $bob = new TheBuilder(PostStub::class);
        return $bob->getResults();
    }

    /** @test */
    public function it_gives_BadMethodCallException_if_model_isnt_configured_for_scout()
    {
        $request = request();
        
        // search queryfilter set
        $request->merge([
            'search' => 'bob'
        ]);

        $this->expectException(BadMethodCallException::class);

        $posts = new TheBuilder(CommentStub::class);
    }

    /** @test */
    public function it_returns_scout_instance_if_search_is_called()
    {
        $this->withoutExceptionHandling();

        $request = request();

        // search queryfilter set
        $request->merge([
            'search' => 'bob'
        ]);
        $this->assertTrue($request->has('search'));
        $this->assertEquals('bob', $request->get('search'));

        $posts = new TheBuilder(PostStub::class);

        $this->assertFalse($posts->isEloquent());
        $this->assertCount(0, $posts->getResults());
    }

    /** @test */
    public function it_returns_collection_of_search_results()
    {
        $this->withoutExceptionHandling();

        $postOne = factory(PostStub::class)->create([
            'title' => 'bob'
        ]);
        $postTwo = factory(PostStub::class)->create([
            'title' => 'shouldnotfind'
        ]);
        $postThree = factory(PostStub::class)->create([
            'title' => 'alsoshouldnotfind'
        ]);
        $this->assertCount(3, PostStub::all());

        // search queryfilter set
        $returnedData = $this->getQueryStringResults([
            'search' => 'bob'
        ]);

        $this->assertCount(1, $returnedData);
        $this->assertEquals($postOne->id, $returnedData->first()->id);
        $this->assertCount(3, PostStub::all());
    }

    /** @test */
    public function it_can_return_scout_results_with_relationships()
    {
        $this->withoutExceptionHandling();

        $postOne = factory(PostStub::class)->create([
            'title' => 'bob'
        ]);
        $postTwo = factory(PostStub::class)->create([
            'title' => 'shouldnotfind'
        ]);
        factory(CommentStub::class, 5)->create([
            'post_stub_id' => $postOne->id
        ]);

        // setting query string values this is the same as /endpoint?with=comments
        $returnedData = $this->getQueryStringResults([
            'search' => 'bob',
            'with' => 'comments'
        ]);

        $this->assertCount(1, $returnedData->toArray());
        $this->assertCount(5, $returnedData->toArray()[0]['comments']);
        
    }
}