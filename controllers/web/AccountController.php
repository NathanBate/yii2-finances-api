<?php

namespace app\controllers\web;

use BaseApi;
use yii\helpers\ArrayHelper;
use app\models\AccountModel;
use baseapi\controllers\BaseController; // use this if you want to disable web viewing
use \yii\web\Controller as YiiController;  // use this if you want to see this in web view
use app\helpers\Action;

class AccountController extends BaseController
{

    /**
     * Returns a list of accounts.
     *
     * @return array
     */
    public function actionIndex()
    {
        return [
            "accounts" => ArrayHelper::index(AccountModel::getAccounts(),'accountId'),
        ];
    }

    /**
     * Add a Bank Account
     *
     * @return bool[]
     */
    public function actionAdd()
    {
        Action::allowAdminOnly();

        $account = new AccountModel();
        if ($account->load(BaseApi::$app->request->post(), '') && $account->add()) {
            return ["success" => true];
        } else {
            return ["success" => false];
        }
    }

    /**
     * Updates the Specified Account
     *
     * @return bool[]
     */
    public function actionUpdate()
    {
        Action::allowAdminOnly();

        $account = new AccountModel();
        if ($account->load(BaseApi::$app->request->post(), '') && $account->updateAccount()) {
            return ["success" => true];
        } else {
            return ["success" => false];
        }
    }

    /**
     * Flag the specified account as trashed
     *
     * @param $id
     * @return array
     */
    public function actionTrashBankAccount($id)
    {
        Action::allowAdminOnly();
        Action::checkParam($id, -1);
        return AccountModel::trashBankAccount($id);
    }

    /**
     * Get a list of users who have access to this account
     *
     * @param int $id
     * @param int $userid
     * @return array
     */
    public function actionAddBankAccountUser($id = -1, $userid = -1)
    {
        Action::allowAdminOnly();
        Action::checkParam($id, -1);
        Action::checkParam($userid, -1);
        return AccountModel::addBankAccountUsers($id, $userid);
    }

    /**
     * Removes the specified account user
     *
     * @param int $id
     * @return array
     */
    public function actionRemoveBankAccountUser($id = -1)
    {
        Action::allowAdminOnly();
        Action::checkParam($id, -1);
        return AccountModel::removeBankAccountUsers($id);
    }

    /**
     * Gets the account users on this account and keys an array of them by the user id
     *
     * @param int $id
     * @return array
     */
    public function actionGetAccountUsers($id = -1)
    {
        Action::allowAdminOnly();
        Action::checkParam($id, -1);
        return AccountModel::getAccountUsers($id);
    }

}