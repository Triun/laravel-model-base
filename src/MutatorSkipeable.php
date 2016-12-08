<?php

namespace Triun\ModelBase;


/**
 * Class MutatorSkipeable
 * @package Triun\ModelBase
 */
trait MutatorSkipeable
{
    /**
     * Set a given attribute on the model, without using the mutator.
     * Add phone type functionality.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    protected function setAttributeWithoutMutator($key, $value)
    {
        if ($value !== null) {
            // If an attribute is listed as a "date", we'll convert it from a DateTime
            // instance into a form proper for storage on the database tables using
            // the connection grammar's date format. We will auto set the values.
            if (in_array($key, $this->getDates()) || $this->isDateCastable($key)) {
                $value = $this->fromDateTime($value);
            }

            if ($this->isJsonCastable($key)) {
                $value = $this->asJson($value);
            }
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get a plain attribute (not a relationship), without using the mutator.
     * Add phone type functionality.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValueWithoutMutator($key)
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
        if (in_array($key, $this->getDates()) && ! is_null($value)) {
            return $this->asDateTime($value);
        }

        return $value;
    }
}