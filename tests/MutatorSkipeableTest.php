<?php


class MutatorSkipeableTest extends TestCase
{
    /**
     * @test
     */
    function it_gets_an_attribute_value_without_mutator()
    {
        $model = $this->getPost();

        $model->title = 'Some title';

        $this->assertEquals(
            $model->getAttribute('title'),
            'Title: Some title'
        );

        $this->assertEquals(
            $model->title,
            'Title: Some title'
        );

        $this->assertEquals(
            array_get($model->getAttributes(), 'title'),
            'Some title'
        );

        $this->assertEquals(
            $model->getAttributeValueWithoutMutator('title'),
            'Some title'
        );
    }
}
