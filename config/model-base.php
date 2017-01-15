<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modifiers
    |--------------------------------------------------------------------------
    |
    | What do we want to be done by our generator.
    | The minimum modifiers are already included and cannot be removed.
    |
    */

    'modifiers' => [],

    /*
    |--------------------------------------------------------------------------
    | Model Base Class Info
    |--------------------------------------------------------------------------
    |
    | Some parameters to define the class.
    |
    | - namespace: Namespace for the model base objects.
    | - extends: The class which the Model Bases should be extended from.
    | - renames: Tables which should take a different name for the model class ('table_name' => 'ModelName').
    | - prefix: Model Class Prefix.
    | - suffix: Model Class Suffix.
    | - override: In case that the file already exists, whether if we should override it, not, or ask for confirmation.
    |
    */

    'namespace' => 'App\\ModelsBases',
    'extends' => 'Eloquent', // \Illuminate\Database\Eloquent\Model::class,
    'renames' => [],
    'prefix' => '',
    'suffix' => 'Base',
    'override' => true, // true | false | 'confirm' (set to null if you want to prompt a confirmation question).

    /*
    |--------------------------------------------------------------------------
    | Model Class Info
    |--------------------------------------------------------------------------
    |
    | Some parameters to define the class.
    |
    | - namespace: Namespace for the model base objects.
    | - prefix: Model Class Prefix.
    | - suffix: Model Class Suffix.
    |
    */

    'model' => [
        'namespace' => 'App\\Models',
        'prefix' => '',
        'suffix' => '',
        'save' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Support for custom Doctrine dbal mapping types
    |--------------------------------------------------------------------------
    |
    | This setting allow you to map any custom database type (that you may have
    | created using CREATE TYPE statement or imported using database plugin
    | / extension to a Doctrine type.
    |
    | Each key in this array is a name of the Doctrine2 DBAL Platform. Currently valid names are:
    | 'postgresql', 'db2', 'drizzle', 'mysql', 'oracle', 'sqlanywhere', 'sqlite', 'mssql'
    |
    | This name is returned by getName() method of the specific Doctrine/DBAL/Platforms/AbstractPlatform descendant
    |
    | The value of the array is an array of type mappings. Key is the name of the custom type,
    | (for example, "jsonb" from Postgres 9.4) and the value is the name of the corresponding Doctrine2 type (in
    | our case it is 'json_array'. Doctrine types are listed here:
    | http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html
    |
    | So to support jsonb in your models when working with Postgres, just add the following entry to the array below:
    |
    | "postgresql" => [
    |       "jsonb" => "json_array",
    |  ],
    |
    | More info:
    | http://symfony.com/doc/current/doctrine/dbal.html
    |
    | 'doctrine' => [
    |     'dbal' => [
    |       'mapping_types' => [
    |
    |        ],
    |     ],
    | ],
    |
    | - doctrine.dbal.mapping_types: Mapping applied for all drivers.
    | - doctrine.dbal.driver_mapping_types: Mapping applied for a specific driver.
    | - doctrine.dbal.real_length: Overwrite the default length with the real one.
    | - doctrine.dbal.real_tinyint: Overwrite the type of tinyint to the type given, instead of boolean, if the length
    |   it's bigger than 1.
    |
    */

    'doctrine' => [
        'dbal' => [
          'mapping_types' => [

           ],
          'driver_mapping_types' => [
              'mysql' => [
                  'enum' => 'string',
                  //'tinyint' => 'smallint',
              ],
              'mssql' => [
                  'xml' => 'string',
              ],
          ],
          'real_length' => true,
          'real_tinyint' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Behaviours
    |--------------------------------------------------------------------------
    |
    | The following behaviours will define how to generate the skeleton of the
    | base models.
    |
    | Sometimes this items could also assign the value of an existent eloquent
    | attribute, as in the case of 'snakeAttributes'.
    |
    | - snakeAttributes: whether or not we want to normalize attributes as snake case.
    | - dates: whether or not we want to use Carbon objects for dates.
    | - dateFormat: Eloquent date format. Default null.
    | - softDeletes: whether or not we want to implement eloquent soft deleted in the models.
    |
    */

    'snakeAttributes'   => true,
    'dates'             => true,
    'dateFormat'        => null,
    'softDeletes'       => true, // Not implemented

    /*
    |--------------------------------------------------------------------------
    | Camel case to snake case compatibility
    |--------------------------------------------------------------------------
    |
    | This modifier allow the model to use databases with camelCase column names as snake case in the model, so
    | $model->column_name could access columnName in the database.
    |
    | In order to do manual renaming, you can fill camel_to_snake.
    |
    | This could be useful in order to correct snake_names conversion singularities, for example:
    | IP should be ip, and not i_p.
    |
    | Notice that the values of the array will be set as lower case.
    |
    */

    'camel_to_snake' => [],

    /*
    |--------------------------------------------------------------------------
    | Cast
    |--------------------------------------------------------------------------
    |
    | - cast: The attributes that should be casted to native types.
    |
    | Ex (this is already implemented by laravel):
    |
    |  'cast' => [
    |      [
    |          'field'      => 'field_name',
    |          'table'      => 'table_name',        // optional
    |          'connection' => 'connection_name',   // optional (the default could be null)
    |          'db_type'    => 'text',              // optional
    |          'cast_type'  => 'array',
    |      ],
    |  ],
    |
    */

    'cast' => [],

    /*
    |--------------------------------------------------------------------------
    | Eloquent Timestamps
    |--------------------------------------------------------------------------
    |
    | With timestamps configuration we can specify alternative values for the CREATED_AT and UPDATED_AT constants.
    |
    | With 'force', we will try to find any of the items in the array, and will use the first occurrence that we find.
    |
    | With 'alternative', we will try to find the default value 'created_at' or 'updated_at', and only if we don't find
    | it, we will try to use the first occurrence in 'alternative' array.
    |
    */

    'timestamps' => [
        'CREATED_AT' => [
            'force' => [],
            'alternative' => [],
        ],
        'UPDATED_AT' => [
            'force' => [],
            'alternative' => [],
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Eloquent Attributes
    |--------------------------------------------------------------------------
    |
    | - hidden: The attributes that should be hidden for arrays.
    | - fillable: Rules to make fields fillable.
    |   - fillable.tables: Array of tables which should have all the fields fillable.
    |   - fillable.no_fill: fields that never must be as fillable.
    |
    */

    'hidden' => [
        'password',
        'remember_token',
    ],

    'fillable' => [
        'tables' => [],
        'no_fill' => [
            'id',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bulk
    |--------------------------------------------------------------------------
    |
    | Tables rules used by bulk generation.
    |
    | - except: Array of tables that doesn't need a Model.
    |
    */

    'bulk' => [
        'except' => ['migration'],
    ],

];
