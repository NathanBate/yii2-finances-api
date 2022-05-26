<?php

use yii\db\Migration;

/**
 * Class m201222_134613_rename_budgets_trashed_column
 */
class m201222_134613_rename_budgets_trashed_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn("{{%budgets}}","trashed", "archived");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201222_134613_rename_budgets_trashed_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201222_134613_rename_budgets_trashed_column cannot be reverted.\n";

        return false;
    }
    */
}
