<?php

namespace Triun\ModelBase\Modifiers;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

/**
 * @link https://laravel.com/docs/5.5/authentication
 * @link https://github.com/laravel/laravel/blob/master/app/User.php
 */
class AuthModifier extends ModifierBase
{
    protected array $default = [
        'Authenticatable'  => true,
        'Authorizable'     => true,
        'CanResetPassword' => true,
        'Notifiable'       => true,
        'fillable'         => ['name', 'email', 'password'],
    ];

    public function apply(Skeleton $skeleton): void
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

    public function params(): ?array
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

    protected function addToArrayProperty(string $name, Property $property): void
    {
        // Add to dates array
        if (!in_array($name, $property->value)) {
            $property->value[] = $name;
        }
    }
}
