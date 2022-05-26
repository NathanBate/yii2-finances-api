<?php

use yii\db\Migration;

/**
 * Class m210402_155327_change_float_to_double
 */
class m210402_155327_change_float_to_double extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn("{{%statements}}","beginningBalance",$this->double());
        $this->alterColumn("{{%statements}}","endingBalance",$this->double());
        $this->alterColumn("{{%transactions}}","amount",$this->double());
        $this->alterColumn("{{%transactions}}","total",$this->double());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210402_155327_change_float_to_double cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210402_155327_change_float_to_double cannot be reverted.\n";

        return false;
    }
    */
}
