<?php

namespace Statamic\Data\Users;

use Statamic\API\Arr;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\Contracts\Data\Users\User;
use Statamic\Contracts\Data\Users\UserFactory as UserFactoryContract;

class UserFactory implements UserFactoryContract
{
    protected $data = [];
    protected $username;
    protected $email;

    /**
     * @return $this
     */
    public function create()
    {
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function username($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function email($email)
    {
        if (Config::get('users.login_type') === 'email') {
            $this->username = $email;
        } else {
            $this->email = $email;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return $this->get()->save();
    }

    /**
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function get()
    {
        $data = $this->data;
        $password = Arr::pull($data, 'password');

        $user = app(User::class);
        $user->username($this->username);
        $user->data($data);

        if ($password) {
            $user->password($password);
        }

        if (Config::get('users.login_type') === 'username') {
            $user->email($this->email);
        }

        $user->syncOriginal();

        return $user;
    }
}
