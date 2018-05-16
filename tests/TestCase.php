<?php

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TeamTNT\Scout\TNTSearchScoutServiceProvider::class,
            Laravel\Scout\ScoutServiceProvider::class,
        ];
    }

    public function setUp()
    {
        parent::setUp();

        Eloquent::unguard();

        $this->withFactories(realpath(__DIR__ . '/factories'));
    }

    public function tearDown()
    {
        \Schema::drop('posts');
        \Schema::drop('comments');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('scout.driver', 'tntsearch');
        $app['config']->set('scout.tntsearch', [
            'storage'  => storage_path(), //place where the index files will be stored
            'fuzziness' => false,
            'fuzzy' => [
                'prefix_length' => 2,
                'max_expansions' => 50,
                'distance' => 2
            ],
            'asYouType' => false,
            'searchBoolean' => false,
        ]);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        \Schema::create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        \Schema::create('comments', function ($table) {
            $table->increments('id');
            $table->text('body');
            $table->unsignedInteger('post_stub_id');
            $table->timestamps();
        });
    }
}