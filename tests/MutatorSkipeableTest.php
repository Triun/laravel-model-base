<?php

/**
 * Class MutatorSkipeableTest
 * @see \Triun\ModelBase\MutatorSkipeable
 */
class MutatorSkipeableTest extends TestCase
{
    /**
     * Original string given.
     *
     * @var string
     */
    protected $original = 'Some title';

    /**
     * Expected mutation when set.
     *
     * @var string
     */
    protected $setterMutator = 'Native title';

    /**
     * Expected mutation when get.
     *
     * @var string
     */
    protected $getterMutator = 'Formatted title';

    /**
     * @test
     */
    function it_has_getter_and_setter_mutators()
    {
        $model = $this->getPost();

        $model->title = $this->original;

        $native = array_get($model->getAttributes(), 'title');

        // Native (setter mutation)
        $this->assertEquals(
            $this->setterMutator,
            $native
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->getAttribute('title')
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->title
        );
    }

    /**
     * @test
     */
    function it_sets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->run_setAttributeWithoutMutator('title', $this->original);

        $native = array_get($model->getAttributes(), 'title');

        // Native (setter mutation)
        $this->assertEquals(
            $this->original,
            $native
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->getAttribute('title')
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->title
        );
    }

    /**
     * @test
     */
    function it_gets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->title = $this->original;

        $native = array_get($model->getAttributes(), 'title');

        // Native (setter mutation)
        $this->assertEquals(
            $this->setterMutator,
            $native
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->getAttribute('title')
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->title
        );

        $this->assertEquals(
            $native,
            $model->getAttributeValueWithoutMutator('title')
        );

        $this->assertEquals(
            $this->setterMutator,
            $model->getAttributeValueWithoutMutator('title')
        );
    }

    /**
     * @test
     */
    function it_sets_and_gets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->run_setAttributeWithoutMutator('title', $this->original);

        $native = array_get($model->getAttributes(), 'title');

        // Native (setter mutation)
        $this->assertEquals(
            $this->original,
            $native
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->getAttribute('title')
        );

        // Getter mutation
        $this->assertEquals(
            $this->getterMutator,
            $model->title
        );

        $this->assertEquals(
            $this->original,
            $model->getAttributeValueWithoutMutator('title')
        );
    }

    /**
     * @test
     */
    function it_does_casts()
    {
        $model = $this->getPost();

        $metadata = [
            'foo' => 'bar',
        ];

        $json = json_encode($metadata);

        $model->run_setAttributeWithoutMutator('metadata', $metadata);

        $native = array_get($model->getAttributes(), 'metadata');

        // Native (setter mutation)
        $this->assertEquals(
            $json,
            $native
        );

        $this->assertEquals(
            $metadata,
            $model->getAttributeValueWithoutMutator('metadata')
        );
    }

    /**
     * @test
     */
    function it_does_dates()
    {
        $model = $this->getPost();

        $carbon = \Carbon\Carbon::now();

        $datetime = $carbon->toDateTimeString();

        $model->run_setAttributeWithoutMutator('updated_at', $carbon);

        $native = array_get($model->getAttributes(), 'updated_at');

        // Native (setter mutation)
        $this->assertEquals(
            $datetime,
            $native
        );

        $this->assertInstanceOf(
            \Carbon\Carbon::class,
            $model->getAttributeValueWithoutMutator('updated_at')
        );

        $this->assertEquals(
            $carbon,
            $model->getAttributeValueWithoutMutator('updated_at')
        );
    }

    /**
     * Retrieve a example table.
     *
     * @return Post
     */
    protected function getPost()
    {
        $post = new Post();

        $post->title = 'Some title';
        $post->metadata = [
            'foo' => 'bar',
        ];
        $post->save();

        return $post;
    }
}

/**
 * Class Post
 *
 * @property string $title
 * @property array|object $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Post extends \Illuminate\Database\Eloquent\Model
{
    use \Triun\ModelBase\MutatorSkipeable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Set a given attribute on the model, without using the mutator.
     * Add phone type functionality.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function run_setAttributeWithoutMutator($key, $value)
    {
        return $this->setAttributeWithoutMutator($key, $value);
    }

    /**
     * Get a plain attribute (not a relationship), without using the mutator.
     * Add phone type functionality.
     *
     * @param  string  $key
     * @return mixed
     */
    public function run_getAttributeValueWithoutMutator($key)
    {
        return $this->getAttributeValueWithoutMutator($key);
    }

    /**
     * @return string
     */
    public function getTitleAttribute()
    {
        return str_replace(['Native', 'Some'], 'Formatted', $this->attributes['title']);
    }

    /**
     * @param $value
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = str_replace('Some', 'Native', $value);
    }
}
