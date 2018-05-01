<?php

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [];
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