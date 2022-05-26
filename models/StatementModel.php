<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;
use yii\db\Query;
use app\helpers\BaseApiHelper as Helper;

class StatementModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statements}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['statementDate','account','beginningBalance','endingBalance'], 'required'],
            ['reconciled','default', 'value' => 'N'],
        ];
    }

    /**
     * Returns a list of statements
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getStatements() {
        return static::find()
            ->all();
    }

    public static function viewStatement($id)
    {
        $statement = (new Query())
            ->select([
                "*",
                "s.id as statementId",
                "b.id as budgetId",
                "b.name as budgetName",
                "DATE_FORMAT(statementDate,'%c/%e/%y') AS statementDateFormatted"
            ])
            ->from("{{%statements}} s")
            ->join("LEFT JOIN", "{{%budgets}} b", "b.id = s.budget ")
            ->where("s.id = $id")
            ->one();
        Helper::nullCheck($statement);

        if (isset($statement['beginningBalance'])) {
            $statement['beginningBalance'] = round($statement['beginningBalance'], 2);
        }

        if (isset($statement['endingBalance'])) {
            $statement['endingBalance'] = round($statement['endingBalance'], 2);
        }

        /**
         * Get the previous statement if there is one.  If there isn't, then return a
         * zero balance
         */

        /**
         * prep the date variables
         */
        $date = new \DateTime($statement['statementDate']);
        $interval = new \DateInterval('P1M');
        $date->sub($interval);
        $year = date("Y", strtotime($date->format('Y-m-d')));
        $month = date("m", strtotime($date->format('Y-m-d')));
        $account = $statement['account'];

        /**
         * Try to get the previous statement
         */
        $previousStatement = StatementModel::find()
            ->where("YEAR(statementDate) = $year AND MONTH(statementDate) = $month AND account = $account")
            ->one();

        if (isset($previousStatement['endingBalance'])) {
            $previousStatement['endingBalance'] = round($previousStatement['endingBalance'], 2);
        }

        /**
         * If there was no statement, return 0
         */
        if ($previousStatement === null) {
            $previousStatement = [
                "endingBalance" => 0.00
            ];
        }

        /**
         * Get the available budgets
         */
        if (($statement['budget'] !== null) && ($statement['budget'] !="")) {
            $availableBudgets = BudgetModel::find()
                ->where("id != " . $statement['budget'] . " AND archived='N'")
                ->all();
        } else {
            $availableBudgets = BudgetModel::find()
                ->where("archived='N'")
                ->all();
        }

        if ($availableBudgets === null) {
            $availableBudgets = [];
        } else {
            $availableBudgets = Helper::qSelect($availableBudgets,"name","id");
        }


        return [
            "success" => true,
            "statement" => $statement,
            "previousStatement" => $previousStatement,
            "availableBudgets" => $availableBudgets,
        ];
    }

    /**
     * Returns the statement for the specified month on the specified account
     * @param int $year
     * @param int $month
     * @param int $account
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getStatement($year, $month, $account) {
        $statement = static::find()
            ->where("YEAR(statementDate) = $year AND MONTH(statementDate) = $month AND account = $account")
            ->one();

        return $statement;
    }

    /**
     * Adds a statement
     *
     * @return bool
     */
    public function add()
    {
        if ($this->validate()) {
            $this->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update the Specified Statement
     *
     * @return array
     */
    public static function updateStatement()
    {
        $updatedStatement = Helper::getPost();
        $current = StatementModel::findOne($updatedStatement['id']);
        $current->reconciled = $updatedStatement['reconciled'];
        $current->beginningBalance = $updatedStatement['beginningBalance'];
        if (isset($updatedStatement['budget'])) {
            $current->budget = $updatedStatement['budget'];
        }
        $current->save();

        TransactionModel::totalStatement($current);
        return Helper::successOnly();
    }

    /**
     * Detaches the budget from the specified statement
     *
     * @param $statementId
     * @return array
     */
    public static function detachBudget($statementId)
    {
        $statement = static::findOne($statementId);
        Helper::nullCheck($statement, "Statement Not Found!");
        $statement->budget = null;
        $statement->save();
        return Helper::successOnly();
    }


}