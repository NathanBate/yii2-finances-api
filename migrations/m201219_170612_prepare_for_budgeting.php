<?php

use yii\db\Migration;

/**
 * Class m201219_170612_prepare_for_budgeting
 */
class m201219_170612_prepare_for_budgeting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%statements}}", "budget", $this->integer());
        $this->renameColumn("{{%budgets}}", "year", "user");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201219_170612_prepare_for_budgeting cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201219_170612_prepare_for_budgeting cannot be reverted.\n";

        return false;
    }
    */
}
