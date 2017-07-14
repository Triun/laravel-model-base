<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connections
    |--------------------------------------------------------------------------
    |
    | Connection specific configurations.
    |
    | The configurations set for a specific connection would would be merged with the outside connections
    | configurations.
    |
    | Example:
    |
    |   'connections' => [
    |       'my-connection-1' => [
    |           'namespace' => 'App\\ModelsBases\\MyConnection1',
    |           'extends' => \My\Laravel\ModelBase::class,
    |           'renames' => [
    |               'deliveriesAddresses'   => 'delivery_address',
    |               'salesSync'             => 'sale_sync',
    |           ],
    |           'prefix' => '',
    |           'suffix' => 'Base',
    |           'override' => true,
    |
    |           'model' => [
    |               'namespace' => 'App\\Models\\MyConnection1',
    |               'prefix' => '',
    |               'suffix' => '',
    |               'save' => true,
    |           ],
    |       ],
    |   ],
    |
    | Note: If multi connections are set, you may want to move the auth to the respective connection instead to the
    | default configuration.
    |
    | To run a specific connection:
    |
    | ```
    | php artisan make:model-base-bulk --connection=my-connection-1
    | ```
    |
    */
    'connections' => [],

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
    'extends' => \Illuminate\Database\Eloquent\Model::class, // 'Eloquent'|\Illuminate\Database\Eloquent\Model::class,
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
    | - softDeletes: whether or not we want to implement eloquent soft deleted in the models (see DELETED_AT config).
    |
    */

    'snakeAttributes'   => true,
    'dates'             => true,
    'dateFormat'        => null,
    'softDeletes'       => true, // See DELETED_AT configuration.

    /*
    |--------------------------------------------------------------------------
    | Column
    |--------------------------------------------------------------------------
    |
    | Processes applied to the columns.
    |
    */

    'column' => [

        /*
        |--------------------------------------------------------------------------
        | Column Aliases
        |--------------------------------------------------------------------------
        |
        | You may want to add some aliases, maybe because the table name has a standard naming like prefixes.
        |
        | With the aliases you can hide the original names (which will remain active by magic methods), and create
        | aliases instead.
        |
        | The execution of this rules are:
        |
        | If `except` has a match, it will skip it.
        |
        | If `force` has a match, it will set this alias and skip the other rules.
        |
        | The rest of the rules will be processed in the following order:
        |
        | 1. pre: Rename it before the other rules are applied.
        | 2. prefix: If the column name start with any of the words in the list, it will remove it.
        | 3. suffix: If the column name ends with any of the words in the list, it will remove it.
        | 4. post: Rename it after the other rules are applied.
        |
        */

        'aliases' => [
            // If it match, it will skip it.
            'except' => [],
            // If there is a match, none of the following renames rules will be processed.
            'force' => [],
            // Rename it before the other rules are applied.
            'pre' => [],
            // If the column name start with any of the words in the list, it will remove it.
            'prefix' => [],
            // If the column name ends with any of the words in the list, it will remove it.
            'suffix' => [],
            // Rename it after the other rules are applied.
            'post' => [],
        ],

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
    ],

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
    | You can use multiple values with '|'. Example: 'field_1|field_2'
    |
    | You can also use the shell wildcard pattern.
    | Example: "*gr[ae]y" would match grey, gray, or anything that finish in any of those.
    | @see \fnmatch
    | @link http://php.net/manual/en/function.fnmatch.php
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
    | Soft Deletes - DELETED_AT
    |--------------------------------------------------------------------------
    |
    | With DELETED_AT configuration we can specify alternative values for the constants.
    |
    | With 'force', we will try to find any of the items in the array, and will use the first occurrence that we find.
    |
    | With 'alternative', we will try to find the default value 'deleted_at', and only if we don't find
    | it, we will try to use the first occurrence in 'alternative' array.
    |
    | @see https://laravel.com/docs/5.3/eloquent#soft-deleting
    |
    */

    'DELETED_AT' => [
        'force' => [],
        'alternative' => [],
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
    | Auth support
    |--------------------------------------------------------------------------
    |
    | Implements laravel's authentication for a model.
    |
    | Uses the same mechanism as the user models comes with by default.
    |
    | More info:
    | https://laravel.com/docs/5.4/authentication
    | https://github.com/laravel/laravel/blob/master/app/User.php
    | https://github.com/laravel/framework/blob/5.2/src/Illuminate/Foundation/Auth/User.php
    |
    | - auth: list of tables that should implement the authenticable configuration.
    |   - auth.Authenticatable: Optional. Whether implement Authenticatable trait and Contract (default true).
    |   - auth.CanResetPassword: Optional. Whether implement CanResetPassword trait and Contract (default true).
    |   - auth.Authorizable: Optional. Whether implement Authorizable trait and Contract (default false).
    |   - auth.fillable: Optional. Fillable fields (default ['email', 'password']).
    |
    | Example 1:
    | 'auth' => [
    |     'users',
    |     'customers',
    | ],
    |
    | Example 2:
    | 'auth' => [
    |     'users' => [
    |         'CanResetPassword' => false,
    |         'Authorizable' => true,
    |     ],
    | ],
    |
    | Example 3:
    | 'auth' => [
    |     'users' => [
    |         'Authenticatable' => true,
    |         'CanResetPassword' => false,
    |         'Authorizable' => false,
    |         'fillable' => ['email', 'password'],
    |     ],
    | ],
    |
    | Note:
    | This tool is not going to make this configuration for you, and doesn't check if the configuration is correct
    | either. You can update the auth models or tables used by laravel's in config/auth.php.
    | More info about auth customization: https://laravel.com/docs/5.2/authentication#adding-custom-guards
    |
    | Note:
    | If you use more than one connection, we recommend to leave this array empty, and add it to the respective
    | connection.
    |
    */

    'auth' => [
        'users', // For multi connections: Move this array to the specific connection and left here an empty array.
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
        'except' => ['migrations', 'sessions'],
    ],

];
