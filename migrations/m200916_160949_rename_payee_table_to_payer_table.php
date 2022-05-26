<?php

use yii\db\Migration;

/**
 * Class m200916_160949_rename_payee_table_to_payer_table
 */
class m200916_160949_rename_payee_table_to_payer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable("{{%payees}}", "{{%payers}}");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200916_160949_rename_payee_table_to_payer_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200916_160949_rename_payee_table_to_payer_table cannot be reverted.\n";

        return false;
    }
    */
}
