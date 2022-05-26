<?php

namespace app\helpers;

use BaseApi;
use app\helpers\User;

Class Action {

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

}



