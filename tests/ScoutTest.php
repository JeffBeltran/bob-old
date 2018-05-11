<?php

use JeffBeltran\Bob\TheBuilder;

class ScoutTest extends TestCase
{

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

        $request = request();
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
        $request->merge([
            'search' => 'bob'
        ]);

        $posts = new TheBuilder(PostStub::class);
        
        $returnedPosts = $posts->getResults();
        $this->assertCount(1, $returnedPosts);
        $this->assertEquals($postOne->id, $returnedPosts->first()->id);
        $this->assertCount(3, PostStub::all());
    }
}