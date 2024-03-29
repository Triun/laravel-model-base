<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\ParameterType;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use ReflectionClass;
use Triun\ModelBase\Definitions\Column;
use Triun\ModelBase\Definitions\PhpDocTag;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

class PhpDocModifier extends ModifierBase
{
    /**
     * @var string[]
     */
    protected array $defaultMixing = [
        '\\Illuminate\\Database\\Query\\Builder',
        '\\Illuminate\\Database\\Eloquent\\Builder',
    ];

    public function apply(Skeleton $skeleton): void
    {
        $BuilderReflectionClass = new ReflectionClass(Builder::class);

        $this->columnsPHPDoc($skeleton, $BuilderReflectionClass);

        $this->mixinPhpDoc($skeleton);
    }

    /**
     * Add properties tags.
     */
    protected function columnsPhpDoc(Skeleton $skeleton, ReflectionClass $BuilderReflectionClass): void
    {
        $columns      = $this->table()->getColumns();
        $descriptions = $this->getDescriptions($columns);

        foreach ($columns as $column) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                '$' . $column->publicName,
                'property',
                $column->phpDocType,
                trim($descriptions[$column->publicName] . ' ' . $column->getComment())
            ));
        }
    }

    /**
     * @param Column[] $columns
     *
     * @return string[]
     */
    private function getDescriptions(array $columns): array
    {
        $descriptions = [];
        $maxLength    = 0;
        foreach ($columns as $column) {
            $descriptions[$column->publicName] = $this->columnsPhpDocCommentType($column);
            $maxLength                         = max($maxLength, Str::length($descriptions[$column->publicName]));
        }

        foreach ($descriptions as $publicName => $value) {
            $descriptions[$publicName] = str_pad($value, $maxLength);
        }

        return $descriptions;
    }

    private function columnsPhpDocCommentType(Column $column): string
    {
        $comment = $column->dbType;

        if ($column->unsigned) {
            $comment = 'unsigned ' . $comment;
        }

        if ($column->nullable) {
            $comment .= '|null';
        }

        if (null !== ($default = $column->getDefault())) {
            if (null === $default) {
                $default = 'null';
            } else {
                switch ($column->getType()->getBindingType()) {
                    case ParameterType::NULL:
                        $default = 'null';
                        break;
                    case ParameterType::STRING:
                        $default = '"' . $default . '"';
                        break;
                    case ParameterType::BOOLEAN:
                        $default = $default ? 'true' : 'false';
                        break;
                    case ParameterType::LARGE_OBJECT:
                        $default = 'large object';
                        break;
                    case ParameterType::BINARY:
                        $default = 'binary';
                        break;
                    case ParameterType::INTEGER:
                    default:
                        // As it is
                }
            }
            $comment .= ' (default: ' . $default . ')';
        }

        return $comment;
    }

    /**
     * Add `mixin` phpDoc tags.
     */
    protected function mixinPhpDoc(Skeleton $skeleton): void
    {
        $mixins = array_merge($this->config('mixin', []), $this->defaultMixing);

        foreach ($mixins as $mixin) {
            $skeleton->addPhpDocTag(new PhpDocTag(
                null,
                'mixin',
                $mixin
            ));
        }
    }
}
