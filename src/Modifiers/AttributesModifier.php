<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;

class AttributesModifier extends ModifierBase
{
    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        // ConnectionModifier
        // connection:      null

        // ClassBaseModifier
        // table:           null
        // primaryKey:      'id'
        // keyType:         3
        // incrementing:    true

        // Pagination properties
        // perPage:         15

        // TimestampsModifier
        // timestamps:      true

        // DatesModifier
        // dates:           []
        // dateFormat:      null

        // Relation properties
        // touches:         []
        // with:            []

        // Attributes properties:
        // hidden:          []
        // visible:         []
        // appends:         []
        // fillable:        []
        // guarded:         ['*']
        // casts:           []
        // snakeAttributes: true

        // ?
        // observables:     []
        // morphClass:      null

        // Others:  attributes, original, relations, exists, wasRecentlyCreated, resolver, dispatcher, booted,
        //          globalScopes, unguarded, mutatorCache, manyMethods.

        // Apply
        $this->hidden($skeleton);
        $this->fillable($skeleton);
        $this->casts($skeleton);

        $this->setProperty($skeleton, 'snakeAttributes', $this->config('snakeAttributes'));
    }

    /**
     * Apply eloquent hidden attributes.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    protected function hidden(Skeleton $skeleton)
    {
        $fields = [];

        foreach ($this->table()->getColumns() as $column) {
            $name = $column->getName();
            foreach ($this->config('hidden', []) as $rule) {
                if ($this->match($rule, $name)) {
                    $fields[] = $name;
                }
            }
        }

        $skeleton->property('hidden')->setValue($fields);
    }

    /**
     * Apply eloquent fillable.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function fillable(Skeleton $skeleton)
    {
        if (array_search($this->table()->getName(), $this->config('fillable.tables', [])) === false) {
            return;
        }

        $fields = [];
        foreach ($this->table()->getColumns() as $column) {
            $name = $column->getName();
            foreach ($this->config('fillable.no_fill', []) as $rule) {
                if (!$this->match($rule, $name)) {
                    $fields[] = $name;
                }
            }
        }

        $skeleton->property('fillable')->setValue($fields);
    }

    /**
     * Apply eloquent casting.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    protected function casts(Skeleton $skeleton)
    {
        $cast = $skeleton->property('casts');

        foreach ($this->table()->getColumns() as $column) {
            $cast->value[$column->getName()] = $column->castType;
        }
    }
}
