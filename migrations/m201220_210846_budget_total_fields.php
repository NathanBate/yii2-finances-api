<?php

use yii\db\Migration;

/**
 * Class m201220_210846_budget_total_fields
 */
class m201220_210846_budget_total_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%budgets}}", "incomeTotal", $this->float());
        $this->addColumn("{{%budgets}}", "expenseTotal", $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201220_210846_budget_total_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201220_210846_budget_total_fields cannot be reverted.\n";

        return false;
    }
    */
}
