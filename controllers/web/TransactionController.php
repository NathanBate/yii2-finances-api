<?php

namespace app\controllers\web;

use baseapi\controllers\BaseController;
use yii\web\controller as YiiController;

use app\models\TransactionModel;

use app\helpers\Action;
use app\helpers\BaseApiHelper as Helper;


class TransactionController extends BaseController
{
    /**
     * Gathers transaction data for a view transaction screen
     *
     * @param int $year
     * @param int $month
     * @param int $account
     * @return array
     */
    public function actionIndex($year=-1, $month=-1, $account=-1)
    {
        Helper::checkParam($year, -1);
        Helper::checkParam($month, -1);
        Helper::checkParam($account, -1);
        return TransactionModel::getTransactions($year, $month, $account);
    }

    /**
     * Get's the transaction by the id that was specified
     *
     * @param int $id
     * @return array
     */
    public function actionView($id=-1)
    {
        Helper::checkParam($id, -1);
        return TransactionModel::viewTransaction($id);
    }

    /**
     * Updates the Specified Transaction
     *
     * @return array
     */
    public function actionUpdate()
    {
        return TransactionModel::updateTransaction();
    }

    /**
     * Gets the recipients, the payers, and the scheduled transactions for the add
     * transaction page.  The scheduled transactions are shown on a per-account basis.
     *
     * @param int $account - Account ID
     * @param int $statement
     * @return array
     */
    public function actionGetScheduledTransactions($account=-1, $statement=-1)
    {
        Helper::checkParam($account, -1);
        Helper::checkParam($statement, -1);
        return TransactionModel::getScheduledTransactions($account, $statement);
    }

    /**
     * Adds income and expense transactions.
     *
     * @return array
     */
    public function actionAddTransaction()
    {
        return TransactionModel::addTransaction();
    }

    /**
     * Delete the specified transaction
     *
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
        Helper::checkParam($id, -1);
        return TransactionModel::deleteTransaction($id);
    }

    /**
     * Adds the posted scheduled transaction
     *
     * @return array
     */
    public function actionAddToScheduledTransactionList()
    {
        return TransactionModel::addToScheduledTransactionList();
    }

    /**
     * Get the specified scheduled transaction
     *
     * @param int $id
     * @return array
     */
    public function actionGetScheduledTransaction($id = -1)
    {
        Helper::checkParam($id, -1);
        return TransactionModel::getScheduledTransaction($id);
    }

    /**
     * Update the posted scheduled transaction
     *
     * @return array
     */
    public function actionUpdateScheduledTransaction()
    {
        return TransactionModel::updateScheduledTransaction();
    }

    /**
     * Get the recipients for the specified account
     *
     * @param int $id
     * @return array
     */
    public function actionGetRecipients($id=-1)
    {
        Helper::checkParam($id, -1, "actionGetRecipients - id - $id");
        return TransactionModel::getRecipients($id);
    }

    /**
     * Get the payers for the specified account
     *
     * @param int $id
     * @return array
     */
    public function actionGetPayers($id=-1)
    {
        Helper::checkParam($id, -1, "actionGetPayers - id - $id");
        return TransactionModel::getPayers($id);
    }

}