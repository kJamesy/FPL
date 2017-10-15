<?php

namespace App\Permissions;

use App\User;
use Carbon\Carbon;

class UserPermissions
{

    /**
     * All available permission keys
     * @var array
     */
    public static $permissionKeys = [
        'create', 'read', 'update', 'delete'
    ];

    /**
     * Get/cache permissions for the supplied user
     * @param User $user
     * @param bool $refresh
     * @return mixed
     */
    public static function getCachedUserPermissions(User $user, $refresh = false)
    {
        if ( $refresh )
            cache()->forget("user_{$user->id}_permissions");

        return cache()->remember("user_{$user->id}_permissions", Carbon::now()->addDay(), function() use($user) {
            return static::getAllUserPermissions($user);
        });
    }

    /**
     * Get all permissions for specified user
     * @param User $user
     * @return array
     */
    public static function getAllUserPermissions(User $user)
    {
        $permissions = [];
        $permissionKeys = static::$permissionKeys;
        $models = static::getPolicyOwnerClasses();

        if ( $user && $models && $permissionKeys ) {
            foreach( $models as $model ) {
                $modelKey = static::getModelShortName($model);

                foreach ( $permissionKeys as $permissionKey ) {
                    $permissions["{$permissionKey}_{$modelKey}"] = $user->can($permissionKey, $model);
                }
            }
        }

        return $permissions;
    }

    /**
     * Get permission from array of specified permissions
     * @param $permissions
     * @param $permission
     * @return bool
     */
    public static function permissionExists($permissions, $permission)
    {
        return array_key_exists($permission, $permissions) ? $permissions[$permission] : false;
    }

    /**
     * Get all registered policies
     * @return array
     */
    public static function getPolicies()
    {
        return (array) resolve('AuthService')->getPolicies();
    }

    /**
     * Get all registered policies' owner classes
     * @return array
     */
    public static function getPolicyOwnerClasses()
    {
        return array_keys(static::getPolicies());
    }

    /**
     * Extract pluralised short class name from a namespaced model class
     * @param $modelClass
     * @return string
     */
    public static function getModelShortName($modelClass)
    {
        try {
            $reflect = new \ReflectionClass($modelClass);
            $class = preg_replace('/\B([A-Z])/', '_$1', $reflect->getShortName());

            return str_plural(strtolower($class));
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Return model given a model short name
     * @param $shortName
     * @return null
     */
    public static function getModelFromShortName($shortName)
    {
        $shortName = str_replace('_', '', str_singular($shortName));
        $shortName = ucfirst($shortName);
        $class = "\\App\\{$shortName}";

        return class_exists($class) ? new $class : null;
    }


    /**
     * Determine if supplied user has supplied permission
     * @param User $user
     * @param $permission
     * @return bool
     */
    public static function hasPermission(User $user, $permission)
    {
        $meta = json_decode($user->meta);

        if ( $meta && property_exists( $meta, $permission ) )
            return $meta->{$permission} == true;

        return false;
    }

    /**
     * Find uppercase letters occurrence in a string
     * @param $string
     * @return array
     */
    public static function getUppercaseLetters($string)
    {
        $number = preg_match_all('/[A-Z]/', $string, $matches, PREG_OFFSET_CAPTURE);
        return [$number, $matches];
    }
}