<?php

namespace Triun\ModelBase\Definitions;

use InvalidArgumentException;
use Triun\ModelBase\Exception\SkeletonUseAliasException;
use Triun\ModelBase\Exception\SkeletonUseNameException;
use Triun\ModelBase\Utils\SkeletonUtil;

class Skeleton
{
    /**
     * Name for the class, included namespace.
     */
    public string $className;

    public bool $isAbstract = false;

    /**
     * Which class extends.
     */
    public ?string $extends = null;

    /**
     * Which classes should be imported.
     *
     * @var string[]
     */
    protected array $uses = [];

    /**
     * Which interfaces implements.
     *
     * @var string[]
     */
    protected array $interfaces = [];

    /**
     * Which traits uses.
     *
     * @var string[]
     */
    protected array $traits = [];

    public string $phpDocComment;

    /**
     * Class PhpDoc tags
     *
     * @var PhpDocTag[]
     */
    protected array $phpDocTags = [];

    /**
     * Model Base Skeleton constants.
     *
     * @var Constant[]
     */
    protected array $constants = [];

    /**
     * Model Base Skeleton properties.
     *
     * @var Property[]
     */
    protected array $properties = [];

    /**
     * Model Base Skeleton methods.
     *
     * @var Method[]
     */
    protected array $methods = [];

    /**
     * Get the full namespace name.
     */
    public function getNamespace(): string
    {
        return trim(implode('\\', array_slice(explode('\\', $this->className), 0, -1)), '\\');
    }

    /**
     * Get the class "basename".
     */
    public function getClassBasename(): string
    {
        return class_basename($this->className);
    }

    /**
     * @return string[]
     */
    public function uses(): array
    {
        // return $this->uses;
        $uses = [];

        // Add the extended
        $uses[$this->extends] = $this->extends;

        // Add the implements
        $this->usesCompilationAppend($uses, $this->interfaces);

        // Add the traits
        $this->usesCompilationAppend($uses, $this->traits);

        // Add other uses
        $this->usesCompilationAppend($uses, $this->uses);

        // Fix duplicated basenames to be used (from different namespaces).
        /*$basenames = [];
        foreach ($uses as $use) {
            $basename = class_basename($use);
            if (isset($basenames[$basename])) {
                $count = 0;
                do {
                    $count++;
                    $altBasename = $basename.'_'.$count;
                } while (isset($basenames[$altBasename]));
                $basenames[$altBasename] = $use;

                $uses[$use] = "$use as $altBasename";
            } else {
                $basenames[$basename] = $use;
            }
        }*/

        asort($uses);

        return $uses;
    }

    protected function usesCompilationAppend(&$uses, array $names): void
    {
        foreach ($names as $name => $alias) {
            if (isset($uses[$alias])) {
                throw new SkeletonUseNameException($name, $alias, $uses[$alias]);
            }

            if (in_array($name, $uses)) {
                throw new SkeletonUseAliasException($alias, $name, array_search($name, $uses));
            }

            if (class_basename($name) === $alias) {
                $uses[$alias] = $name;
            } else {
                $uses[$alias] = $name . ' as ' . $alias;
            }
        }
    }

    /**
     * @return string[]
     */
    public function interfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @return string[]
     */
    public function traits(): array
    {
        return $this->traits;
    }

    /**
     * @return PhpDocTag[]
     */
    public function phpDocTags(): array
    {
        return $this->phpDocTags;
    }

    /**
     * @return Constant[]
     */
    public function constants(): array
    {
        return $this->constants;
    }

    /**
     * @return Property[]
     */
    public function properties(): array
    {
        return $this->properties;
    }

    /**
     * @return Method[]
     */
    public function methods(): array
    {
        return $this->methods;
    }

    public function hasUse(string $key): bool
    {
        return isset($this->uses[$key])
               || ($this->extends === $key)
               || isset($this->interfaces[$key])
               || isset($this->traits[$key]);
    }

    public function hasInterface(string $key): bool
    {
        return isset($this->interfaces[$key]);
    }

    public function hasTrait(string $key): bool
    {
        return isset($this->traits[$key]);
    }

    public function hasPhpDocTag(string $key): bool
    {
        return isset($this->phpDocTags[$key]);
    }

    public function hasConstant(string $key): bool
    {
        return isset($this->constants[$key]);
    }

    public function hasProperty(string $key): bool
    {
        return isset($this->properties[$key]);
    }

    public function hasMethod(string $key): bool
    {
        return isset($this->methods[$key]);
    }

    /**
     * Get phpDocTag.
     *
     * @throws InvalidArgumentException
     */
    public function phpDocTag(string $key): ?PhpDocTag
    {
        if (!$this->hasPhpDocTag($key)) {
            throw new InvalidArgumentException("PhpDocTag $key not defined");
        }

        return $this->phpDocTags[$key];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function constant(string $key): ?Constant
    {
        if (!$this->hasConstant($key)) {
            throw new InvalidArgumentException("Constant $key not defined");
        }

        return $this->constants[$key];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function property(string $key): ?Property
    {
        if (!$this->hasProperty($key)) {
            throw new InvalidArgumentException("Property $key not defined");
        }

        return $this->properties[$key];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function method(string $key): ?Method
    {
        if (!$this->hasMethod($key)) {
            throw new InvalidArgumentException("Method $key not defined");
        }

        return $this->methods[$key];
    }

    public function addUse(string $className, ?string $alias = null): static
    {
        // $this->uses[$className] = $alias?: basename($className);
        $this->appendClass($this->uses, $className, $alias);

        return $this;
    }

    public function addInterface(string $interfaceName, ?string $alias = null): static
    {
        if (!interface_exists($interfaceName)) {
            throw new InvalidArgumentException("$interfaceName is not a valid interface");
        }

        $this->appendClass($this->interfaces, $interfaceName, $alias);

        // TODO: Get info but don't load the components or it would give an error when trying to set the value.
        //SkeletonUtil::loadReflection($this, $interfaceName);

        return $this;
    }

    public function addTrait(string $traitName, string $alias = null): static
    {
        if (!trait_exists($traitName)) {
            throw new InvalidArgumentException("$traitName is not a valid trait");
        }

        $this->appendClass($this->traits, $traitName, $alias);

        SkeletonUtil::loadReflection($this, $traitName);

        return $this;
    }

    protected function appendClass(array &$array, string $name, ?string $alias = null, string $type = 'class'): static
    {
        [$name, $alias] = static::splitNameAndAlias($name, $alias);

        if (!isset($array[$name])) {
            $array[$name] = $alias;
        } elseif ($array[$name] !== $alias) {
            throw new SkeletonUseAliasException($alias, $name, $array[$name]);
        }

        return $this;
    }

    protected static function splitNameAndAlias(string $name, ?string $alias = null): array
    {
        if (str_contains($name, ' as ')) {
            return explode(' as ', $name);
        }

        return [$name, null === $alias ? class_basename($name) : $alias];
    }

    public function addPhpDocTag(PhpDocTag $value): static
    {
        // If the tag doesn't have names...
        if ($value->getName() === null && in_array($value->tag, ['mixin'])) {
            $this->phpDocTags[] = $value;

            return $this;
        }

        if (isset($this->properties[$value->getName()])) {
            throw new InvalidArgumentException("The property {$value->getName()} already exists");
        }

        $this->phpDocTags[$value->getName()] = $value;

        return $this;
    }

    public function addConstant(Constant $value): static
    {
        if (isset($this->constants[$value->name])) {
            throw new InvalidArgumentException("The constant {$value->name} already exists");
        }

        $this->constants[$value->name] = $value;

        return $this;
    }

    public function addProperty(Property $value): static
    {
        if (isset($this->properties[$value->name])) {
            throw new InvalidArgumentException("The property {$value->name} already exists");
        }

        $this->properties[$value->name] = $value;

        return $this;
    }

    public function addMethod(Method $value): static
    {
        if (isset($this->methods[$value->name])) {
            $item = $this->methods[$value->name];

            if ($item->isDirty()) {
                //dump($item->value);
                //dump($value->value);
                // TODO: Another way to catch this issue.
                echo "The method {$value->name} already exists."
                     . " Please, update it instead of try to create it again." . PHP_EOL;
                /*throw new InvalidArgumentException(
                    "The method {$value->name} already exists. Please, update it instead of try to create it again."
                );*/
            }

            if (($value->modifiers_id !== null && $item->modifiers_id != $value->modifiers_id)
                || ($item->modifiers === null && $item->modifiers != $value->modifiers)) {
                // TODO: review this error message
                throw new InvalidArgumentException(
                    "{$this->className} and {$value->file} define the same method ({$value->name})"
                    . " in the composition of {$this->className}."
                    . " However, the definition differs and is considered incompatible."
                );

                // App\ModelsBases\Master\AddressBase and HW\Validation\ModelValidable define
                // the same property ($rules) in the composition of App\ModelsBases\Master\AddressBase.
                // However, the definition differs and isconsidered incompatible.
                // Class was composed in /var/www/app/app/ModelsBases/Master/AddressBase.php on line 308
            }
        }

        $this->methods[$value->name] = $value;

        return $this;
    }

    public function removeConstant(string $key): static
    {
        unset($this->constants[$key]);

        return $this;
    }

    public function removeProperty(string $key): static
    {
        unset($this->methods[$key]);

        return $this;
    }

    public function removeMethod(string $key): static
    {
        unset($this->methods[$key]);

        return $this;
    }

    /**
     * @return Constant[]
     */
    public function dirtyConstants(): array
    {
        return array_filter($this->constants, function ($value) {
            /* @var $value Constant */
            return $value->isDirty();
        });
    }

    /**
     * @return Property[]
     */
    public function dirtyProperties(): array
    {
        return array_filter($this->properties, function ($value) {
            /* @var $value Property */
            return $value->isDirty();
        });
    }

    /**
     * @return Method[]
     */
    public function dirtyMethods(): array
    {
        return array_filter($this->methods, function ($value) {
            /* @var $value Method */
            return $value->isDirty();
        });
    }

    /**
     * Get the skeleton as an array, with the model base constants, properties and methods.
     */
    public function toArray(): array
    {
        return [
            'constants'  => $this->constants,
            'properties' => $this->properties,
            'methods'    => $this->methods,
        ];
    }

    /**
     * Get the values of the skeleton object.
     */
    public function defaults(): array
    {
        return [
            'constants'  => array_map(function ($value) {
                return $value->default;
            }, $this->constants),
            'properties' => array_map(function ($value) {
                return $value->default;
            }, $this->properties),
            'methods'    => $this->methods,
        ];
    }

    /**
     * Get the values of the skeleton object.
     */
    public function values(): array
    {
        return [
            'constants'  => array_map(function ($value) {
                return $value->value;
            }, $this->constants),
            'properties' => array_map(function ($value) {
                return $value->value;
            }, $this->properties),
            'methods'    => $this->methods,
        ];
    }

    /**
     * Get the dirty items.
     */
    public function dirty(): array
    {
        return [
            'constants'  => array_filter($this->constants, function ($value) {
                /* @var $value Constant */
                return $value->isDirty();
            }),
            'properties' => array_filter($this->properties, function ($value) {
                /* @var $value Constant */
                return $value->isDirty();
            }),
            'methods'    => $this->methods,
        ];
    }
}
