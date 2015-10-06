<?php

class Dialog extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $dialog_id;

    /**
     *
     * @var integer
     */
    public $name;

    /**
     *
     * @var string
     */
    public $time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'dialog';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Dialog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Dialog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function initialize()
    {
        $app->hasMany("dialog_id", "UserDialog", "dialog_id");
        $app->hasMany("dialog_id", "Message", "dialog_id");
    }

}
