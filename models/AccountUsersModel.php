<?php

namespace app\models;

use app\helpers\User;
use BaseApi;
use yii\db\ActiveRecord;
use app\models\AccountModel;

class AccountUsersModel extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%accountusers}}';
    }

    public function getAccountInfo()
    {
        // a comment has one customer
        return $this->hasOne(AccountModel::className(), ['id' => 'account']);
    }


}