<?php

class Message extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $message_id;

    /**
     *
     * @var string
     */
    public $login;

    /**
     *
     * @var integer
     */
    public $dialog_id;

    /**
     *
     * @var string
     */
    public $time;

    /**
     *
     * @var string
     */
    public $text;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'message';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Message[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Message
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


    public function initialize()
    {
        $app->belongsTo("dialog_id", "Dialog", "dialog_id");
        $app->belongsTo("login", "User", "login");
        $app->hasMany("message_id", "Image", "message_id");
    }

}
