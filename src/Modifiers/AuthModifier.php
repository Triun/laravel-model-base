<?php

namespace Triun\ModelBase\Modifiers;

use Triun\ModelBase\Lib\ModifierBase;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Definitions\Property;

use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Class UserModifier
 *
 * @package Triun\ModelBase\Modifiers
 *
 * @link    https://laravel.com/docs/5.5/authentication
 * @link    https://github.com/laravel/laravel/blob/master/app/User.php
 */
class AuthModifier extends ModifierBase
{
    /**
     * Default values.
     *
     * @var array
     */
    protected $default = [
        'Authenticatable'  => true,
        'Authorizable'     => true,
        'CanResetPassword' => true,
        'Notifiable'       => true,
        'fillable'         => ['name', 'email', 'password'],
    ];

    /**
     * Apply the modifications of the class.
     *
     * @param \Triun\ModelBase\Definitions\Skeleton
     */
    public function apply(Skeleton $skeleton)
    {
        $params = $this->params();

        if ($params !== null) {
            if ($params['Authenticatable']) {
                $skeleton->addTrait(Authenticatable::class);
                $skeleton->addInterface(AuthenticatableContract::class, 'AuthenticatableContract');
            }

            if ($params['Authorizable']) {
                $skeleton->addTrait(Authorizable::class);
                $skeleton->addInterface(AuthorizableContract::class, 'AuthorizableContract');
            }

            if ($params['CanResetPassword']) {
                $skeleton->addTrait(CanResetPassword::class);
                $skeleton->addInterface(CanResetPasswordContract::class, 'CanResetPasswordContract');
            }

            if ($params['Notifiable']) {
                $skeleton->addTrait(Notifiable::class);
            }

            // Add to $fillable array
            $fillable = $skeleton->property('fillable');
            foreach ($params['fillable'] as $name) {
                $this->addToArrayProperty($name, $fillable);
            }
        }
    }

    /**
     * @return array|null
     */
    public function params()
    {
        $table = $this->table()->getName();

        $tables = $this->config('auth', []);

        // Table name only, return all defaults.
        if (in_array($table, $tables)) {
            return $this->default;
        }

        if (isset($tables[$table])) {
            return array_merge($this->default, $tables[$table]);
        }

        return null;
    }

    /**
     * @param string                                $name
     * @param \Triun\ModelBase\Definitions\Property $property
     */
    protected function addToArrayProperty($name, Property $property)
    {
        // Add to dates array
        if (array_search($name, $property->value) === false) {
            $property->value[] = $name;
        }
    }
}
