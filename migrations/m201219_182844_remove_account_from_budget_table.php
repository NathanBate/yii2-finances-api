<?php

use yii\db\Migration;

/**
 * Class m201219_182844_remove_account_from_budget_table
 */
class m201219_182844_remove_account_from_budget_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn("{{%budgets}}", "account");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201219_182844_remove_account_from_budget_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201219_182844_remove_account_from_budget_table cannot be reverted.\n";

        return false;
    }
    */
}
