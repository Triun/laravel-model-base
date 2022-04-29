<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * @see \Triun\ModelBase\AddOns\MutatorSkipeable
 */
class MutatorSkipeableTest extends TestCase
{
    /**
     * Original string given.
     *
     * @var string
     */
    protected string $original = 'Some title';

    /**
     * Expected mutation when set.
     *
     * @var string
     */
    protected string $setterMutator = 'Native title';

    /**
     * Expected mutation when got.
     *
     * @var string
     */
    protected string $getterMutator = 'Formatted title';

    /**
     * @test
     */
    public function it_has_getter_and_setter_mutators()
    {
        $model = $this->getPost();

        $model->title = $this->original;

        $native = Arr::get($model->getAttributes(), 'title');

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
    public function it_sets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->run_setAttributeWithoutMutator('title', $this->original);

        $native = Arr::get($model->getAttributes(), 'title');

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
    public function it_gets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->title = $this->original;

        $native = Arr::get($model->getAttributes(), 'title');

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
    public function it_sets_and_gets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->run_setAttributeWithoutMutator('title', $this->original);

        $native = Arr::get($model->getAttributes(), 'title');

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
    public function it_does_casts()
    {
        $model = $this->getPost();

        $metadata = [
            'foo' => 'bar',
        ];

        $json = json_encode($metadata);

        $model->run_setAttributeWithoutMutator('metadata', $metadata);

        $native = Arr::get($model->getAttributes(), 'metadata');

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
    public function it_does_dates()
    {
        $model = $this->getPost();

        $carbon = Carbon::now();

        $format = $model->run_getDateFormat();
//        $datetime = $carbon->toDateTimeString();
        $datetime = $carbon->format($format);
        $carbon   = new Carbon($datetime); // In 7.1 it gets microseconds (ex; 2017-01-16T09:35:07.720258+0800)

        $model->run_setAttributeWithoutMutator('updated_at', $carbon);

        $native = Arr::get($model->getAttributes(), 'updated_at');

        // Native (setter mutation)
        $this->assertEquals(
            $datetime,
            $native
        );

        $this->assertInstanceOf(
            Carbon::class,
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
    protected function getPost(): Post
    {
        $post = new Post();

        $post->title    = 'Some title';
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
 * @property string       $title
 * @property array|object $metadata
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 */
class Post extends \Illuminate\Database\Eloquent\Model
{
    use \Triun\ModelBase\AddOns\MutatorSkipeable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * @return string
     */
    public function run_getDateFormat(): string
    {
        return $this->getDateFormat();
    }

    /**
     * Set a given attribute on the model, without using the mutator.
     * Add phone type functionality.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function run_setAttributeWithoutMutator(string $key, mixed $value): static
    {
        return $this->setAttributeWithoutMutator($key, $value);
    }

    /**
     * Get a plain attribute (not a relationship), without using the mutator.
     * Add phone type functionality.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function run_getAttributeValueWithoutMutator(string $key): mixed
    {
        return $this->getAttributeValueWithoutMutator($key);
    }

    /**
     * @return string
     */
    public function getTitleAttribute(): string
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
