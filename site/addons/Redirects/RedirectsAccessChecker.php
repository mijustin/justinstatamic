<?php

namespace Statamic\Addons\Redirects;

use Statamic\API\Role;
use Statamic\Contracts\Data\Users\User;
use Statamic\Extend\Extensible;

class RedirectsAccessChecker
{
    use Extensible;

    public function hasAccess(User $user)
    {
        if (!$user) {
            return false;
        }

        $roles = $this->getConfig('access_roles', []);

        if (!count($roles)) {
            return true;
        }

        if ($user->isSuper()) {
            return true;
        }

        foreach ($roles as $roleSlug) {
            $role = Role::whereHandle($roleSlug);
            if (!$role) {
                continue;
            }
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
