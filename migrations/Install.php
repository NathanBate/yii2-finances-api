<?php

namespace app\migrations;

use baseapi\migrations\Install as BaseInstallMigration;

class Install extends BaseInstallMigration
{
    public function safeUp()
    {
        if (parent::safeUp() === true) {
            $this->createAccountsTable();
            $this->createStatementsTable();
            $this->createRecipientsTable();
            $this->createPayeesTable();
            $this->createTransactionsTable();
            $this->createScheduledTransactionsTable();
            $this->createCategoriesTable();
            $this->createTagsTable();
            $this->createBudgetsTable();
            $this->createBudgetItemsTable();
            $this->createSecureFilesTable();
            $this->createAcountUsers();
            $this->createDashboardTable();

        }
        return true;
    }

    public function safeDown()
    {
        if (parent::safeDown() === true) {
            $this->dropTable("{{%accounts}}");
            $this->dropTable("{{%statements}}");
            $this->dropTable("{{%recipients}}");
            $this->dropTable("{{%payees}}");
            $this->dropTable("{{%transactions}}");
            $this->dropTable("{{%scheduledtransactions}}");
            $this->dropTable("{{%categories}}");
            $this->dropTable("{{%tags}}");
            $this->dropTable("{{%budgets}}");
            $this->dropTable("{{%budgetitems}}");
            $this->dropTable("{{%securefiles}}");
            $this->dropTable("{{%accountusers}}");
            $this->dropTable("{{%dashboarditems}}");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create the Accounts Table
     */
    private function createAccountsTable()
    {
        $this->createTable("{{%accounts}}", [
            'id' => $this->primaryKey(),
            'accountName' => $this->string(100),
            'bankName' => $this->string(100),
            'accountNumber' => $this->string(100),
            'slug' => $this->string(100),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Statements Table
     */
    private function createStatementsTable()
    {
        $this->createTable("{{%statements}}", [
            'id' => $this->primaryKey(),
            'statementDate' => $this->date(),
            'beginningBalance' => $this->float(),
            'endingBalance' => $this->float(),
            'account' => $this->integer(),
            'reconciled' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Recipients Table
     */
    private function createRecipientsTable()
    {
        $this->createTable("{{%recipients}}", [
            'id' => $this->primaryKey(),
            'recipientName' => $this->string(100),
            'inactive' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Payees Table
     */
    private function createPayeesTable()
    {
        $this->createTable("{{%payees}}", [
            'id' => $this->primaryKey(),
            'payeeName' => $this->string(100),
            'inactive' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Transactions Table
     */
    private function createTransactionsTable()
    {
        $this->createTable("{{%transactions}}", [
            'id' => $this->primaryKey(),
            'transactionDate' => $this->timestamp()->defaultExpression("CURRENT_TIMESTAMP"),
            'account' => $this->integer(),
            'recipient' => $this->integer(),
            'payee' => $this->integer(),
            'amount' => $this->float(),
            'total' => $this->float(),
            'reconciled' => "ENUM('Y','N') DEFAULT 'N'",
            'note' => $this->text(),
            'statement' => $this->integer(),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Scheduled Transactions Table
     */
    private function createScheduledTransactionsTable()
    {
        $this->createTable("{{%scheduledtransactions}}", [
            'id' => $this->primaryKey(),
            'dayOfMonth' => $this->integer(),
            'amount' => $this->float(),
            'payee' => $this->integer(),
            'recipient' => $this->integer(),
            'account' => $this->integer(),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Categories Table
     */
    private function createCategoriesTable()
    {
        $this->createTable("{{%categories}}", [
            'id' => $this->primaryKey(),
            'name' => $this->string(100),
            'account' => $this->integer(),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Tags Table
     */
    private function createTagsTable()
    {
        $this->createTable("{{%tags}}", [
            'id' => $this->primaryKey(),
            'name' => $this->string(100),
            'account' => $this->integer(),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Create the Budgets Table
     */
    private function createBudgetsTable()
    {
        $this->createTable("{{%budgets}}", [
            'id' => $this->primaryKey(),
            'name' => $this->string(100),
            'account' => $this->integer(),
            'year' => $this->integer(),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Creates the Budget Items Table
     */
    private function createBudgetItemsTable()
    {
        $this->createTable("{{%budgetitems}}", [
            'id' => $this->primaryKey(),
            'budget' => $this->integer(),
            'category' => $this->integer(),
            'tag' => $this->integer(),
            'amount' => $this->float(),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Creates the Secure Files Table
     */
    private function createSecureFilesTable()
    {
        $this->createTable("{{%securefiles}}", [
            'id' => $this->primaryKey(),
            'transaction' => $this->integer(),
            'fileExtension' => $this->string(20),
            'trashed' => "ENUM('Y','N') DEFAULT 'N'",
        ]);
    }

    /**
     * Creates the Account Users Table
     */
    private function createAcountUsers()
    {
        $this->createTable("{{%accountusers}}", [
            'id' => $this->primaryKey(),
            'user' => $this->integer(),
            'account' => $this->integer(),
        ]);
    }

    /**
     * Create Dashboard Table
     */
    private function createDashboardTable()
    {
        $this->createTable("{{%dashboarditems}}", [
            'id' => $this->primaryKey(),
            'user' => $this->integer(),
            'typeId' => $this->integer(),
            'type' => "ENUM('Account','Budget','Report','Statement','Recipients','Payees','Categories','Tags','Files','Accounts','Expenses','Income','PandL','BalanceSheet') DEFAULT 'Account'",
        ]);
    }

}

