<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class AttributesModifier extends ModifierBase
{
    public function apply(Skeleton $skeleton): void
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

    protected function hidden(Skeleton $skeleton): void
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

    public function fillable(Skeleton $skeleton): void
    {
        if (!in_array($this->table()->getName(), $this->config('fillable.tables', []))) {
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

    protected function casts(Skeleton $skeleton): void
    {
        $cast = $skeleton->property('casts');

        foreach ($this->table()->getColumns() as $column) {
            $cast->value[$column->getName()] = $column->castType;
        }
    }
}
