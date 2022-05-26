<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;
use yii\db\Query;

use app\models\AccountUsersModel;
use app\models\UserModel;

use app\helpers\User;
use yii\helpers\ArrayHelper;

class BudgetCategoriesModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','type'], 'required'],
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