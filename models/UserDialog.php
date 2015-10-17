<?php

class UserDialog extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $login;



    /**
     *
     * @var bool
     */
    public $new;


    /**
     *
     * @var integer
     */
    public $dialog_id;

    /**
     *
     * @var integer
     */
    public $id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_dialog';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserDialog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserDialog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function initialize()
    {
        $this->belongsTo("login", "User", "login");
        $this->belongsTo("dialog_id", "Dialog", "dialog_id");
    }

}
