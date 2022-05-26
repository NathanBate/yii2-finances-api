<?php

use yii\db\Migration;

/**
 * Class m201219_193936_add_budget_category_type
 */
class m201219_193936_add_budget_category_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%categories}}", "type", "ENUM('','I','E') DEFAULT ''");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201219_193936_add_budget_category_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201219_193936_add_budget_category_type cannot be reverted.\n";

        return false;
    }
    */
}
