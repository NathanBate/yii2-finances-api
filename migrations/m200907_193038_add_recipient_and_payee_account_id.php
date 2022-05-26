<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m200907_193038_add_recipient_and_payee_account_id
 */
class m200907_193038_add_recipient_and_payee_account_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%recipients}}", "account", $this->integer()->defaultValue(1));
        $this->addColumn("{{%payees}}", "account", $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200907_193038_add_recipient_and_payee_account_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200907_193038_add_recipient_and_payee_account_id cannot be reverted.\n";

        return false;
    }
    */
}
