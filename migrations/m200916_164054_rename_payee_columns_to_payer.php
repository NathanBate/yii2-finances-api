<?php

use yii\db\Migration;

/**
 * Class m200916_164054_rename_payee_columns_to_payer
 */
class m200916_164054_rename_payee_columns_to_payer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn("{{%payers}}", "payeeName", "payerName");
        $this->renameColumn("{{%scheduledtransactions}}", "payee", "payer");
        $this->renameColumn("{{%transactions}}", "payee", "payer");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200916_164054_rename_payee_columns_to_payer cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200916_164054_rename_payee_columns_to_payer cannot be reverted.\n";

        return false;
    }
    */
}
