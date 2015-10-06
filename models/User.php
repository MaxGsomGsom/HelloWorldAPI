<?php

class User extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $login;

    /**
     *
     * @var string
     */
    public $pass;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $info;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return User[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return User
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function initialize()
    {
        $app->hasMany("login", "Message", "login");
        $app->hasMany("login", "Image", "login");
        $app->hasMany("login", "UserDialog", "login");
    }

}
