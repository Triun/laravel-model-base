<?php

namespace Triun\ModelBase\Modifiers;

use Doctrine\DBAL\Types\Type;
use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Utils\SchemaUtil;
use Triun\ModelBase\Definitions\Skeleton;

class RulesModifier extends ModifierBase
{
    /**
     * @var array
     */
    protected $platform_columns;

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        $schema_util = new SchemaUtil($this->connection(), $this->config());

        $this->platform_columns = $schema_util->getPlatformColumns($this->table()->getName());

        $rules = [];
        foreach ($this->table()->getColumns() as $column) {
            $rule = $this->columnRules($column);
            if (!empty($rule)) {
                $rules[$column->publicName] = $rule;
            }
        }

        // TODO: add validable trait?
        if (!$skeleton->hasProperty('rules')) {
            $this->setProperty($skeleton, 'rules', []);
        }

        $skeleton->property('rules')->setValue($rules);
    }

    /**
     * Return validation rules for the given column.
     *
     * @param \Triun\ModelBase\Definitions\Column $column
     *
     * @return string rules
     */
    protected function columnRules($column)
    {
        // https://laravel.com/docs/5.2/validation#available-validation-rules
        $rules = [];

        $platform = $this->platform_columns[$column->getName()];

        // If has autoincrement, it hasn't rules?
        /*if ($column->getAutoincrement()) {
            return false;
        }*/

        if ($column->getNotnull() && $column->getDefault() === null) {
            $rules[] = 'required';
        }

        switch ($column->getType()->getName()) {
            case Type::BOOLEAN:
                $rules[] = 'boolean';
                break;
            case Type::SMALLINT:
            case Type::INTEGER:
            case Type::BIGINT:
                $rules[] = 'integer';
                if ($column->getLength() > 0) {
                    $rules[] = 'max:' . (pow(10, $column->getLength()) - 1);
                }
                break;
            case Type::FLOAT:
//            case Type::DOUBLE:
            case Type::DECIMAL:
//            case Type::MONEY:
                $rules[] = 'numeric';
                break;
            case Type::DATE:
                $rules[] = 'date';
                break;
            case Type::TIME:
                $rules[] = 'date_format:H:i:s';
                break;
            case Type::DATETIME:
            case Type::DATETIMETZ:
                if ($platform['db_type'] === 'timestamp') {
                    $rules[] = 'integer';
                } else {
                    $rules[] = 'date_format:Y-m-d H:i:s';
                }
                break;
            /*case Type::TIMESTAMP:
                $rules[] = 'integer';
                break;*/

            case Type::TARRAY:
            case Type::SIMPLE_ARRAY:
            case Type::JSON_ARRAY:
            case Type::OBJECT:
            case Type::BINARY:
            case Type::BLOB:
            case Type::GUID:
            case Type::STRING:
            case Type::TEXT:
            default: // strings
                $rules[] = 'string';
                if ($column->getLength() > 0) {
                    $rules[] = 'max:'.$column->getLength();
                }

            //$strTransforms['trim'][] = $column->name;
            //$strTransforms['stripslashes'][] = $column->name;
        }

//        if ($column->phpDocType === '\\'.PhoneNumber::class) {
//            $rules[] = 'phone:AUTO,IE';
//        }

        return implode('|', $rules);
    }
}
