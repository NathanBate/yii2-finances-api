<?php

namespace app\models;

use BaseApi;

use yii\db\Query;
use yii\db\ActiveRecord;
use app\models\StatementModel;

use yii\helpers\ArrayHelper;
use app\helpers\BaseApiHelper as Helper;

class TransactionModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%transactions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transactionDate','account','amount','statement'], 'required'],
            ['note','default', 'value' => ''],
        ];
    }

    /**
     * Returns transactions for the specified month on the specified account
     *
     * @param int $year
     * @param int $month
     * @param int $account
     * @param string $order
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getTransactions($year, $month, $account, $order="DESC")
    {
        /**
         * Get the statement for the specified month for this account.
         * If there isn't one yet, create one.  If there is, make sure that the
         * total column in the transactions has been totalled based on the statement.
         * This should only happen initially after an import; however, it will serve as a
         * double check later.
         */
        $statement = StatementModel::getStatement($year, $month, $account);
        if ($statement===null) {
            $statement = static::createBlankStatement($year, $month, $account);
        } else {
            static::checkForEmptyTotal($statement);
        }

        /**
         * Include information about an attached budget
         */
        if (($statement['budget'] === null) || ($statement['budget'] == '')) {
            $budgetAttached = false;
            $budget = [];
        } else {
            $budgetAttached = true;
            $budget = BudgetModel::getBudget($statement['budget']);
        }

        $statementId = $statement['id'];

        /**
         * Get the Transactions
         */
        $transactions = static::transactionsQuery($statementId, $order);

        /**
         * Get a total of all the income transactions
         */
        $incomeQuery = Helper::query()
            ->from("{{%transactions}}")
            ->where("statement = $statementId AND payer is not NULL AND trashed = 'N'");
        $incomeTransactions = $incomeQuery->sum("amount");
        if ($incomeTransactions === null) {
            $incomeTransactions = 0;
        } else {
            $incomeTransactions = intval($incomeTransactions);
        }

        /**
         * Get a total of all expense transactions
         */
        $query = Helper::query()
            ->from("{{%transactions}}")
            ->where("statement = $statementId AND recipient is not NULL AND trashed = 'N'");
        $expenseTransactions = $query->sum("amount");
        if ($expenseTransactions === null) {
            $expenseTransactions = 0;
        } else {
            $expenseTransactions = abs(intval($expenseTransactions));
        }

        $statement = Helper::formatStatement($statement);

        return [
            "success" => true,
            "transactions" => $transactions,
            "statement" => $statement,
            "budgetAttached" => $budgetAttached,
            "budget" => $budget,
            "incomeTransactionsTotal" => $incomeTransactions,
            "expenseTransactionsTotal" => $expenseTransactions,
            "statementId" => $statementId,
        ];

    }

    /**
     * reusable transactions query
     *
     * @param $statementId
     * @param string $order
     * @param int $category
     * @return array
     */
    public static function transactionsQuery($statementId, $order="DESC", $category=null)
    {
        $transactions = Helper::query()
            ->select([
                "*",
                "t.id AS transactionId",
                "DATE_FORMAT(t.transactionDate, '%c/%e/%y') AS transactionDateFormatted",
            ])
            ->from("{{%transactions}} t")
            ->join("LEFT JOIN","{{%recipients}} r ON r.id = t.recipient")
            ->join("LEFT JOIN", "{{%payers}} p ON p.id = t.payer")
            ->filterWhere(["category" => $category])
            ->where("statement = $statementId")
            ->orderBy("t.transactionDate $order, t.total ASC")
            ->all();

        $i = 0;
        foreach ($transactions as $t) {
            $transactions[$i]['amount'] = number_format($t['amount'], 2);
            $transactions[$i]['total'] = number_format($t['total'], 2);
            $i++;
        }

        return $transactions;
    }


    public static function viewTransaction($id)
    {
        $transaction = (new Query())
            ->select([
                "*",
                "t.id AS transactionId",
                "t.account as transactionAccount",
                "r.account as recipientAccount",
                "p.account as payerAccount",
                "DATE_FORMAT(transactionDate,'%c/%e/%y') AS transactionDateFormatted",
                "DATE_FORMAT(transactionDate,'%Y/%m/%d') AS transactionPickerDate",
                "DATE_FORMAT(transactionDate,'%i') AS minuteOrder"
            ])
            ->from("{{%transactions}} t")
            ->join("LEFT JOIN","{{%recipients}} r ON r.id = t.recipient")
            ->join("LEFT JOIN", "{{%payers}} p ON p.id = t.payer")
            ->where("t.id = $id")
            ->one();

        /**
         * Make sure the statement was found
         */
        if ($transaction === null) {
            return [
                "success" => false,
                "reason" => "Transaction not found!"
            ];
        }

        $transaction['amount'] = round($transaction['amount'],2);

        return [
            "success" => true,
            "transaction" => $transaction,
        ];
    }

    /**
     * Adds a favorite
     *
     * @return array
     */
    public static function addTransaction()
    {
        $p = Helper::getPost();
        $transaction = new TransactionModel();
        $transaction->amount = $amount = (float) $p['amount'];

        if (isset($p['category'])) {
            $transaction->category = $p['category'];
        }

        /**
         * Check whether this is income or an expense.  Also check to see if this is a new recipient
         * or payer.
         */
        if ($amount > 0) {
            if ($p['newPayer'] != "") {
                $payer = new PayerModel([
                    "payerName" => $p['newPayer'],
                    "account" => $p['account']
                ]);
                $payer->save();
                $transaction->payer = $payer->id;
            } else {
                $transaction->payer = $p['payer'];
            }
        } else {
            if ($p['newRecipient'] != "") {
                $recipient = new RecipientModel([
                    "recipientName" => $p['newRecipient'],
                    "account" => $p['account']
                ]);
                $recipient->save();
                $transaction->recipient = $recipient->id;
            } else {
                $transaction->recipient = $p['recipient'];
            }

        }

        /**
         * Use the minutes to order the transactions.  If there are more than 59 transactions on this date, then
         * I suppose there would be a problem.  I am not sure if the timestamp would balk and create an error.
         */
        $transactionDate = $p['transactionDate'];
        $minuteOrder = $p['minuteOrder'];
        $transactionDate = $transactionDate . " 01:$minuteOrder:00";
        $transaction->transactionDate = $d = date('Y-m-d H:i:s', strtotime($transactionDate));

        /**
         * Build out the rest of the transaction model
         */
        $transaction->note = $p['note'];
        $transaction->account = $account = $p['account'];
        $transaction->reconciled = $p['reconciled'];

        /**
         * Search for the statement that this transaction should be attached to
         */
        $d = strtotime($d);
        $year = date("Y", $d);
        $month = date("m", $d);
        $statement = StatementModel::getStatement($year, $month, $account);
        if ($statement === null) {
            return [
                "success" => false,
                "reason" => "There is no statement for this month.  Cannot proceed."
            ];
        }
        $transaction->statement = $statement['id'];

        /**
         * Save the Transaction
         */
        $transaction->save();

        /**
         * Trigger a re-totalling of the statement since somethine was added or subtracted.
         */
        TransactionModel::totalStatement($statement);

        return [
            "success" => true
        ];
    }

    /**
     * Update the specified Favorite
     * @param int $id
     * @param array $updated
     *
     * @return array
     */
    public static function updateTransaction()
    {
        $bRetotal = true;
        $updated = Helper::getPost();
        $current = TransactionModel::findOne($updated['id']);

        /**
         * I need to retotal even when I change a date
         */
        /*
        if ($current->amount != $updated['amount']) {
            $bRetotal = true;
            $current->amount = $updated['amount'];
        }
        */

        $current->amount = $updated['amount'];

        if (isset($updated['category'])) {
            $current->category = $updated['category'];
        }


        $transactionDate = $updated['transactionDate'];
        $minuteOrder = $updated['minuteOrder'];
        $transactionDate = $transactionDate . " 01:$minuteOrder:00";
        $current->transactionDate = date('Y-m-d H:i:s', strtotime($transactionDate));

        $current->reconciled = $updated['reconciled'];
        $current->note = $updated['note'];
        $current->save();

        if ($bRetotal === true) {
            $statement = StatementModel::findOne($current['statement']);

            if ($statement !== null) {
                TransactionModel::totalStatement($statement);
            }
        }

        return [
            "success" => true,
        ];
    }

    /**
     * Check for transactions on this statement that have an empty total field.  If there is
     * at least one transaction with an empty total field, re-total all the transactions on the
     * statement.
     *
     * @param $statement
     */
    public static function checkForEmptyTotal($statement)
    {
        /**
         * Look for transactions on this statement with an empty total field.
         */
        $emptyTotals = static::find()
            ->where([
                "statement" => $statement["id"],
                "total" => null
            ])
            ->all();

        /**
         * If there is an empty total field, re-total all the transactions on the
         * statement.
         */
        if (count($emptyTotals) > 0) {
            static::totalStatement($statement);
        }

    }

    /**
     * A change was made to one of the transactions in this statement, a new
     * one was added, or one was removed.  That means that the transactions on this
     * statement need to be re-totalled.
     *
     * @param $statement
     */
    public static function totalStatement($statement)
    {
        /**
         * Get the transactions on this statement
         */
        $transactions = static::find()
            ->where([
                "statement" => $statement["id"],
            ])
            ->orderBy('transactionDate ASC')
            ->all();

        /**
         * Iterate through and re-total starting with the statement beginning
         * balance.
         */
        $runningTotal = $beginningBalance = $statement['beginningBalance'];
        foreach ($transactions as $t) {
            $runningTotal = $runningTotal + $t['amount'];
            $t['total'] = $runningTotal;

            $current = TransactionModel::findOne($t['id']);
            $current->total = $t['total'];
            $current->save();
        }

        /**
         * Update the statement with the new ending balance
         */
        $newStatement = StatementModel::FindOne($statement['id']);
        if ($newStatement !== null) {
            $newStatement->endingBalance = $runningTotal;
            $newStatement->save();
        }

    }

    /**
     * Get the recipients for the specified account
     *
     * @param $accountId
     * @return array
     */
    public static function getRecipients($accountId)
    {
        return Helper::query()
            ->select([
                "*",
                "recipientName AS label",
                "id AS value"
            ])
            ->from("{{%recipients}}")
            ->where("account = $accountId")
            ->all();
    }

    /**
     * Get the payers for the specified account
     *
     * @param $accountId
     * @return array
     */
    public static function getPayers($accountId)
    {
        return Helper::query()
            ->select([
                "*",
                "payerName AS label",
                "id AS value"
            ])
            ->from("{{%payers}}")
            ->where("account = $accountId")
            ->all();
    }

    /**
     * Get the scheduled transactions for the specified account
     *
     * @param int $accountId
     * @param int $statementId
     * @return array
     */
    public static function getScheduledTransactions($accountId, $statementId)
    {
        $scheduled = Helper::query()
            ->select([
                "*",
                "st.id as StId"
            ])
            ->from("{{%scheduledtransactions}} st")
            ->join("LEFT JOIN","{{%recipients}} r ON r.id = st.recipient")
            ->join("LEFT JOIN", "{{%payers}} p ON p.id = st.payer")
            ->where("st.account = $accountId AND st.trashed='N'")
            ->orderBy([
                "st.dayOfMonth" => SORT_ASC,
            ])
            ->all();

        $transactions = static::transactionsQuery($statementId);

        return [
            "scheduled" => $scheduled,
            "payersThisMonth" => Helper::objectify($transactions,"payer"),
            "recipientsThisMonth" => Helper::objectify($transactions, "recipient"),
            "transactions" => $transactions,
        ];
    }

    public static function deleteTransaction($id)
    {
        $transaction = TransactionModel::findOne($id);

        if ($transaction !== null) {

            $d = strtotime($transaction['transactionDate']);
            $year = date("Y", $d);
            $month = date("m", $d);
            $statement = StatementModel::getStatement($year, $month, $transaction['account']);
            if ($statement === null) {
                return [
                    "success" => false,
                    "reason" => "There is no statement for this month.  Cannot proceed."
                ];
            }

            $transaction->delete();
            TransactionModel::totalStatement($statement);

            return ["success"=>true];
        } else {
            return ["success"=>false];
        }
    }

    public static function addToScheduledTransactionList()
    {
        $p = BaseApi::$app->request->post();
        $st = new ScheduledTransactionModel();
        $st->amount = (float) $p['amount'];
        $st->dayOfMonth = $p['dayOfMonth'];
        $st->account = $p['account'];

        if ($p['newPayer'] != "") {
            $payer = new PayerModel([
                "payerName" => $p['newPayer'],
                "account" => $p['account']
            ]);
            $payer->save();
            $st->payer = $payer->id;
        }

        elseif ($p['newRecipient'] != "") {
            $recipient = new RecipientModel([
                "recipientName" => $p['newRecipient'],
                "account" => $p['account']
            ]);
            $recipient->save();
            $st->recipient = $recipient->id;
        }

        elseif ($p['payer'] != "") {
            $st->payer = $p['payer'];
        }

        elseif ($p['recipient'] != "") {
            $st->recipient = $p['recipient'];
        }

        $st->save();

        return [
            "success" => true
        ];
    }

    public static function getScheduledTransaction($id)
    {
        $scheduledTransaction = ScheduledTransactionModel::getScheduledTransaction($id);
        return [
            "success" => true,
            "transaction" => $scheduledTransaction
        ];
    }

    public static function updateScheduledTransaction()
    {
        $updated = BaseApi::$app->request->post();
        $current = ScheduledTransactionModel::findOne($updated['id']);

        $current->dayOfMonth = $updated['dayOfMonth'];
        $current->amount = $updated['amount'];

        if (isset($updated['trashed'])) {
            $current->trashed = $updated['trashed'];
        }

        $current->save();

        return [
            "success" => true
        ];
    }

    /**
     * Re-usable code to create a new blank statement
     *
     * @param $year
     * @param $month
     * @param $account
     * @return \app\models\StatementModel
     */
    private static function createBlankStatement($year, $month, $account)
    {
        $new = new StatementModel();
        $date = $month . "/01/" . $year;
        $new->statementDate = date('Y-m-d H:i:s', strtotime($date));
        $new->beginningBalance = 0.00;
        $new->endingBalance = 0.00;
        $new->account = $account;
        $new->save();
        $new->refresh();
        return $new;
    }

}