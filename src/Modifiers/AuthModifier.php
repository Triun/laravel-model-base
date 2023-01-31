<?php

namespace Triun\ModelBase\Modifiers;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as AuthorizableModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Triun\ModelBase\Definitions\Property;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Lib\ModifierBase;

/**
 * @link https://laravel.com/docs/5.5/authentication
 * @link https://github.com/laravel/laravel/blob/master/app/User.php
 * @see  \Illuminate\Foundation\Auth\User
 */
class AuthModifier extends ModifierBase
{
    protected array $default = [
        'extendsAuthenticatable' => true,
        'Authenticatable'        => true,
        'Authorizable'           => true,
        'CanResetPassword'       => true,
        'MustVerifyEmail'        => true,
        'Notifiable'             => false, // deprecated
        'fillable'               => [], //['name', 'email', 'password'],
    ];

    public function apply(Skeleton $skeleton): void
    {
        $params = $this->params();

        if ($params !== null) {
            if ($params['extendsAuthenticatable']) {
                $skeleton->setExtends(AuthorizableModel::class, 'Authenticatable');
            } else {
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

                if ($params['MustVerifyEmail']) {
                    $skeleton->addTrait(MustVerifyEmail::class);
                }
            }

            if ($params['Notifiable']) {
                Log::warning('The option Notifiable is deprecated in auth, use custom_model_options instead.');
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
