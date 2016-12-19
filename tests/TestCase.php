<?php

use Illuminate\Database\Capsule\Manager as DB;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->setUpDatabase();
        $this->migrateTables();
    }

    protected function setUpDatabase()
    {
        $database = new DB;

        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $database->bootEloquent();
        $database->setAsGlobal();
    }

    protected function migrateTables()
    {
        DB::schema()->create('posts', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    protected function getPost()
    {
        $post = new Post();

        $post->title = 'Some title';
        $post->save();

        return $post;
    }
}

class Post extends \Illuminate\Database\Eloquent\Model
{
    use \Triun\ModelBase\MutatorSkipeable;

    /**
     * @return string
     */
    public function getTitleAttribute()
    {
        return 'Title: '.$this->attributes['title'];
    }
}
