<?php

use yii\db\Migration;

/**
 * Class m201222_225015_add_category_column_to_transaction
 */
class m201222_225015_add_category_column_to_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%transactions}}","category", $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201222_225015_add_category_column_to_transaction cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201222_225015_add_category_column_to_transaction cannot be reverted.\n";

        return false;
    }
    */
}
