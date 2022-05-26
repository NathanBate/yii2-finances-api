<?php

namespace app\helpers;

use app\helpers\User;
use yii\helpers\ArrayHelper;
use BaseApi;

Class BaseApiHelper {

    /**
     * Checking to make sure a user is an admin is a common task.  This reduces and modularizes code.
     */
    public static function allowAdminOnly()
    {
        if (User::isAdmin() == false) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: *");
            echo json_encode([
                "success" => false,
                "message" => "You do not have access to the action!",
            ]);
            exit;

        }
    }

    /**
     * Checking a param value is a common task.  This reduces and modularizes code.
     *
     * @param $param
     * @param $defaultValue
     * @param string $info
     */
    public static function checkParam($param, $defaultValue, $info="")
    {
        if ($param == $defaultValue) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: *");
            echo json_encode([
                "success" => false,
                "message" => "Missing Parameter.",
                "Info" => $info
            ]);
            exit;
        }
    }

    /**
     * Loading a post, validating it according the the model rules, and then saving it
     * is a commonly done task.  This reduces and modularizes code.
     *
     * @param $model
     * @return array
     */
    public static function loadAndAddPost($model)
    {
        if ($model->load(static::getPost(), '') && $model->add()) {
            return ["success" => true];
        } else {
            return ["success" => false];
        }
    }

    /**
     * Short way to get the post
     *
     * @return array
     */
    public static function getPost()
    {
        return BaseApi::$app->request->post();
    }

    /**
     * Shorthand way to return success and nothing else from a model
     *
     * @return array
     */
    public static function successOnly()
    {
        return [
            "success" => true,
        ];
    }

    /**
     * Modularized way to return a failure message
     *
     * @param string $message
     * @return array
     */
    public static function failure($message="")
    {
        return [
            "success" => false,
            "message" => $message
        ];
    }

    /**
     * Returns the passed in data in a way that the quasar q-select can read.
     *
     * @param $data
     * @param $label
     * @param $value
     * @return array
     */
    public static function qSelect($data, $label, $value)
    {
        $prepped = [];
        $index = 0;
        foreach ($data as $d) {
            $prepped[$index]['label'] = $d[$label];
            $prepped[$index]['value'] = $d[$value];
            $index++;
        }
        return $prepped;
    }

    /**
     * Shorthand for
     *
     * @param $data
     * @param $key
     * @return array
     */
    public static function objectify($data, $key)
    {
        return ArrayHelper::index($data, $key);
    }

    /**
     * If a null object was given, spout out an error and halt the application
     *
     * @param $obj
     * @param string $message
     */
    public static function nullCheck($obj, $message="")
    {
        if ($obj === null) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: *");
            echo json_encode([
                "success" => false,
                "message" => $message,
            ]);
            exit;
        }
    }

    /**
     * Shorthand easy to remember for doing a straight-up db query
     *
     * @return \yii\db\Query
     */
    public static function query()
    {
        return (new \yii\db\Query());
    }

    /**
     * Shorthand to format the numbers in a statement
     * @param $statement
     * @return mixed
     */
    public static function formatStatement($statement)
    {
        if (isset($statement['beginningBalance'])) {
            $statement['beginningBalance'] = number_format($statement['beginningBalance'], 2);
        }

        if (isset($statement['endingBalance'])) {
            $statement['endingBalance'] = number_format($statement['endingBalance'], 2);
        }
        return $statement;
    }

}



