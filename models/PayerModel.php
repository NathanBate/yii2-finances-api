<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;

class PayerModel extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payers}}';
    }

    /**
     * Returns a list of the accounts
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getpayers() {
        return static::find()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payerName'], 'required'],
        ];
    }

    /**
     * Adds a favorite
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
     * Update the specified Favorite
     *
     * @return bool
     */
    public function updatepayer()
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

}