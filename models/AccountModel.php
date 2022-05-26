<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;
use yii\db\Query;

use app\models\AccountUsersModel;
use app\models\UserModel;

use app\helpers\User;
use yii\helpers\ArrayHelper;


class AccountModel extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%accounts}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['accountName','bankName','slug'], 'required'],
            ['accountNumber','default', 'value' => ''],
        ];
    }

    /**
     * Returns a list of the accounts
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAccounts()
    {
        /**
         * If the user is an admin, then they can see all the accounts
         */
        $isAdmin = User::isAdmin();
        if ($isAdmin == true) {
            return (new \yii\db\Query())
                ->select([
                    "*",
                    "a.id AS accountId",
                    "au.id AS accountUserId"
                ])
                ->from("{{%accounts}} a")
                ->join("LEFT JOIN","{{%accountusers}} au ON au.account = a.id")
                ->where("a.trashed = 'N'")
                ->all();
        }

        /**
         * If the User is not an admin, then return the accounts they have permission to see
         */
        $userId = BaseApi::$app->user->identity->id;
        return (new \yii\db\Query())
            ->select([
                "*",
                "a.id AS accountId",
                "au.id AS accountUserId"
            ])
            ->from("{{%accounts}} a")
            ->join("LEFT JOIN","{{%accountusers}} au ON au.account = a.id")
            ->where("au.user = $userId AND a.trashed = 'N'")
            ->all();
    }

    /**
     * Adds a Bank Account
     *
     * @return bool
     */
    public function add()
    {
        if ($this->validate()) {
            $this->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update the specified Bank Account
     *
     * @return bool
     */
    public function updateAccount()
    {
        if ($this->validate()) {
            $updated = BaseApi::$app->request->post();
            $current = static::findOne(BaseApi::$app->request->post("id"));
            $current->attributes = $updated;
            $current->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return a list users on the account as well as a list of all the users available to put on the account
     *
     * @param $accountId
     * @return array
     */
    public static function getAccountUsers($accountId)
    {
        //$accountUsers = AccountUsersModel::find()
        $accountUsers = (new Query())
            ->select([
                "au.id AS accountUserId",
                "u.id AS userId",
                "u.firstName",
                "u.lastName",
                "u.email"
            ])
            ->from("{{%accountusers}} au")
            ->join("INNER JOIN", "{{%users}} u", "u.id = au.user")
            ->where("au.account = $accountId")
            ->all();

        $allActiveUsers = UserModel::find()
            ->select("id, firstName, lastName, email")
            ->where("admin = 'N'")
            ->all();

        return [
            "success" => true,
            "accountUsers" => ArrayHelper::index($accountUsers, "userId"),
            "allUsers" => ArrayHelper::index($allActiveUsers, "id"),
        ];
    }

    /**
     * Adds the specified user to the specified account
     *
     * @param $accountId
     * @param $userId
     * @return array
     */
    public static function addBankAccountUsers($accountId, $userId)
    {
        $newAccountUser = new AccountUsersModel();
        $newAccountUser->user = $userId;
        $newAccountUser->account = $accountId;
        $newAccountUser->save();

        return [
            "success" => true
        ];
    }

    /**
     * Removes the specified account user
     *
     * @param $accountUserId
     * @return array
     */
    public static function removeBankAccountUsers($accountUserId)
    {

        $accountUser = AccountUsersModel::find()
            ->where("id = $accountUserId")
            ->one();

        if ($accountUser !== null) {
            $accountUser->delete();
            return [
                "success" => true
            ];
        } else {
            return [
                "success" => false,
                "message" => "account user not found"
            ];

        }
    }

    /**
     * flag the specified account as trashed
     *
     * @param $accountId
     * @return array
     */
    public static function trashBankAccount($accountId)
    {
        $bankAccount = static::find()
            ->where("id = $accountId")
            ->one();

        if ($bankAccount !== null) {
            $bankAccount->trashed = "Y";
            $bankAccount->save();
            return [
                "success" => true
            ];
        } else {
            return [
                "success" => false,
                "message" => "account not found"
            ];
        }
    }

}