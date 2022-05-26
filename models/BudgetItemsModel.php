<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;
use yii\db\Query;

use app\models\AccountUsersModel;
use app\models\UserModel;

use app\helpers\User;
use yii\helpers\ArrayHelper;

class BudgetItemsModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%budgetitems}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['budget','category','amount'], 'required'],
        ];
    }

    /**
     * Adds a budget item
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

}