<?php

class Image extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $img_id;

    /**
     *
     * @var string
     */
    public $img;

    /**
     *
     * @var string
     */
    public $login;

    /**
     *
     * @var integer
     */
    public $message_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'image';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Image[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Image
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function initialize()
    {
        $app->belongsTo("login", "User", "login");
        $app->belongsTo("message_id", "Message", "message_id");
    }

}
