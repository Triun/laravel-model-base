<?php

namespace Triun\ModelBase\AddOns;

use Illuminate\Support\Str;

trait MutatorSkipeable
{
    /**
     * Set a given attribute on the model, without using the mutator.
     * Add phone type functionality.
     */
    protected function setAttributeWithoutMutator(string $key, mixed $value): static
    {
        if ($value !== null) {
            // If an attribute is listed as a "date", we'll convert it from a DateTime
            // instance into a form proper for storage on the database tables using
            // the connection grammar's date format. We will auto set the values.
            if ($value && $this->isDateAttribute($key)) {
                $value = $this->fromDateTime($value);
            }

            if ($this->isJsonCastable($key) && !is_null($value)) {
                $value = $this->castAttributeAsJson($key, $value);
            }
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get a plain attribute (not a relationship), without using the mutator.
     * Add phone type functionality.
     */
    public function getAttributeValueWithoutMutator(string $key): mixed
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if (in_array($key, $this->getDates()) && !is_null($value)) {
            return $this->asDateTime($value);
        }

        return $value;
    }
}
