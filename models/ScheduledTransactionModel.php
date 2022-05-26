<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;

class ScheduledTransactionModel extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scheduledtransactions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dayOfMonth','amount'], 'required'],
            ['payer','default', 'value' => null],
            ['recipient','default', 'value' => null],
            ['account','default', 'value' => null],
            ['trashed','default', 'value' => 'N'],
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

    public static function getScheduledTransaction($id) {
        return (new \yii\db\Query())
            ->select([
                "*",
                "st.id AS scheduledTransactionId"
            ])
            ->from("{{%scheduledtransactions}} st")
            ->join("LEFT JOIN","{{%recipients}} r ON r.id = st.recipient")
            ->join("LEFT JOIN", "{{%payers}} p ON p.id = st.payer")
            ->where("st.id = $id")
            ->one();
    }


}