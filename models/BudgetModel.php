<?php

namespace app\models;

use BaseApi;
use yii\db\ActiveRecord;
use yii\db\Query;

use app\models\AccountUsersModel;
use app\models\UserModel;
use app\models\BudgetItemsModel;
use app\models\BudgetCategoriesModel;

use app\helpers\User;
use yii\helpers\ArrayHelper;
use app\helpers\BaseApiHelper as Helper;

class BudgetModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%budgets}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            ['user','default', 'value' => ''],
        ];
    }

    /**
     * Returns the budgets for the specified account
     *
     * @return array
     */
    public static function getBudgets()
    {
        /**
         * If the user is an admin, they can see all the budgets.  Otherwise, the user can only
         * see the budgets attached to their username.
         */
        $isAdmin = User::isAdmin();
        if ($isAdmin === true) {
            $budgets = static::find()
                ->where("archived = 'N'")
                ->all();
        } else {
            $userId = User::userId();
            $budgets = static::find()
                ->where("user = $userId AND archived = 'N'")
                ->all();
        }

        return [
            "success" => true,
            "budgets" => ArrayHelper::index($budgets,'id'),
        ];
    }

    /**
     * Adds a budget
     *
     * @return bool
     */
    public function add()
    {
        if ($this->validate()) {
            $this->user = User::userId();
            $this->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get's the specified budget with all of the related data
     *
     * @param $budgetId
     * @return array
     */
    public static function getBudget($budgetId)
    {
        $budget = static::findOne($budgetId);
        $budgetIncomeItems = static::budgetItemsQuery($budgetId, 'I');
        $budgetExpenseItems = static::budgetItemsQuery($budgetId, 'E');
        $incomeCategories = static::categoriesQuery("I");
        $expenseCategories = static::categoriesQuery("E");

        $attachedAccounts = StatementModel::find()
            ->where("budget = $budgetId")
            ->all();
        if (count($attachedAccounts) == 0) {
            $budgetAvailableForUpdate = true;
            $attachedAccounts = [];
        } else {
            $budgetAvailableForUpdate = false;
        }

        if ($budget !== null) {
            return [
                "success" => true,
                "budget" => $budget,
                "budgetIncomeItems" => $budgetIncomeItems,
                "usedBudgetIncomeCategories" => Helper::objectify($budgetIncomeItems, "categoryId"),
                "usedBudgetExpensesCategories" => Helper::objectify($budgetExpenseItems, "categoryId"),
                "budgetExpenseItems" => $budgetExpenseItems,
                "incomeCategories" => Helper::qSelect($incomeCategories, "name", "id"),
                "expenseCategories" => Helper::qSelect($expenseCategories, "name", "id"),
                "budgetAvailableForUpdate" => $budgetAvailableForUpdate,
                "attachedAccounts" => $attachedAccounts,
            ];
        } else {
            return Helper::failure("Budget Not Found.");
        }
    }

    /**
     * Re-usable Budget Items Query for both income and expense types
     *
     * @param $budgetId
     * @param $type
     * @return array
     */
    private static function budgetItemsQuery($budgetId, $type)
    {
        $items = Helper::query()
            ->select([
                "*",
                "bi.id as budgetItemId",
                "c.id as categoryId",
                "c.name as categoryName",
            ])
            ->from("{{%budgetitems}} bi")
            ->join("INNER JOIN","{{%categories}} c", "c.id = bi.category")
            ->where("c.type = '$type'")
            ->andWhere("bi.budget = $budgetId")
            ->orderBy("c.name ASC")
            ->all();

        $i = 0;
        foreach ($items as $item) {
            $items[$i]['icon'] = "keyboard_arrow_down";
            $i++;
        }

        return $items;
    }

    /**
     * Re-usable Categories Query for both income and expense types
     *
     * @param $type
     * @return array
     */
    private static function categoriesQuery($type)
    {
        $categories = (new \yii\db\Query())
            ->select([
                "*",
            ])
            ->from("{{%categories}} c")
            ->where("c.type = '$type'")
            ->all();

        if ($categories === null) {
            return [];
        } else {
            return $categories;
        }
    }

    /**
     * Updates the title of the specified budget
     *
     * @param $post
     * @return array
     */
    public static function updateTitle()
    {
        $post = Helper::getPost();
        $currentBudget = static::findOne($post['id']);
        $currentBudget->name = $post['name'];
        $currentBudget->save();

        return Helper::successOnly();
    }

    /**
     * Adds the posted budget item
     *
     * @return array
     */
    public static function addBudgetItem()
    {
        $post = Helper::getPost();
        $budgetItem = new BudgetItemsModel();
        $budgetItem->category = $post['categoryId'];
        $budgetItem->amount = $post['amount'];
        $budgetItem->budget = $post['budget'];
        $budgetItem->save();

        /**
         * Since a new budget item has been added, we need to re-total the budget
         */
        $budgetId = $post['budget'];
        static::totalBudgetItems($budgetId);

        $newBudgetItem = [
            "id" => $budgetItem->id,
            "categoryId" => $budgetItem->category,
            "categoryName" => $post['categoryName'],
            "amount" => $budgetItem->amount,
            "budget" => $budgetItem->budget,
            "budgetItemId" => $budgetItem->id,
        ];

        return [
            "success" => true,
            "budgetItem" => $newBudgetItem,
        ];
    }

    /**
     * Total the income budget items and the expense budget items
     *
     * @param $budgetId
     * @return array
     */
    public static function totalBudgetItems($budgetId)
    {
        /**
         * Get the Specified Budget
         */
        $budget = static::findOne($budgetId);
        if ($budget === null) {
            return Helper::failure("Budget Not Found!");
        }

        /**
         * Get the income items and total them
         */
        $budgetIncomeItems = static::budgetItemsQuery($budgetId, "I");
        if (count($budgetIncomeItems) == 0) {
            $budget->incomeTotal = 0.00;
        } else {
            $incomeTotal = 0;
            foreach ($budgetIncomeItems as $item) {
                $incomeTotal = $incomeTotal + $item["amount"];
            }
            $budget->incomeTotal = $incomeTotal;
        }

        /**
         * Get the expense items and total them
         */
        $budgetExpenseItems = static::budgetItemsQuery($budgetId, "E");
        if (count($budgetExpenseItems) == 0) {
            $budget->expenseTotal = 0.00;
        } else {
            $expenseTotal = 0;
            foreach ($budgetExpenseItems as $item) {
                $expenseTotal = $expenseTotal + $item['amount'];
            }
            $budget->expenseTotal = $expenseTotal;
        }

        $budget->save();
        return Helper::successOnly();
    }

    /**
     * Gets the specified budget item
     *
     * @param $budgetItemId
     * @return array
     */
    public static function getBudgetItem($budgetItemId)
    {
        $budgetItem = (new \yii\db\Query())
            ->select([
                "bi.amount",
                "bi.id as budgetItemId",
                "c.id as categoryId",
                "c.name as categoryName",
            ])
            ->from("{{%budgetitems}} bi")
            ->join("INNER JOIN", "{{%categories}} c", "c.id = bi.category")
            ->where("bi.id = $budgetItemId")
            ->one();
        Helper::nullCheck($budgetItem, "Budget Item Not Found!");
        return [
            "success" => true,
            "budgetItem" => $budgetItem,
        ];
    }

    /**
     * Deletes the specified budget item
     *
     * @param $budgetItemId
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteBudgetItem($budgetItemId)
    {
        $budgetItem = BudgetItemsModel::findOne($budgetItemId);
        Helper::nullCheck($budgetItem, "Budget Item Not Found");
        $budgetId = $budgetItem->budget;
        $budgetItem->delete();
        static::totalBudgetItems($budgetId);
        return Helper::successOnly();
    }

    /**
     * Updates the specified budget item
     *
     * @return array
     */
    public static function updateBudgetItem()
    {
        $newBudgetItem = Helper::getPost();
        $budgetItemId = $newBudgetItem['budgetItemId'];

        $currentBudgetItem = BudgetItemsModel::findOne($budgetItemId);
        Helper::nullCheck($currentBudgetItem, "Budget Item Not Found");

        $currentBudgetItem->amount = $newBudgetItem['amount'];
        $currentBudgetItem->save();

        return Helper::successOnly();
    }

    /**
     * Archives the specified budget
     *
     * @param $budgetId
     * @return array
     */
    public static function archiveBudget($budgetId)
    {
        $budget = static::findOne($budgetId);
        Helper::nullCheck($budget, "Budget Not Found");
        $budget->archived = "Y";
        $budget->save();
        return Helper::successOnly();
    }


    /**
     * Get information on the budget through the statement id
     *
     * @param $statementId
     * @return array
     */
    public static function getBudgetInfoByStatementId($statementId)
    {
        $statement = StatementModel::findOne($statementId);
        Helper::nullCheck($statement, "Statement Not Found!");
        $budgetId = $statement['budget'];
        if (($budgetId === null) || ($budgetId == "")) {
            $incomeBudgetItems = [];
            $expenseBudgetItems = [];
            $budgetAttached = false;
        } else {
            $incomeBudgetItems = Helper::qSelect(static::budgetItemsQuery($budgetId, 'I'), "categoryName" , "categoryId");
            $expenseBudgetItems = Helper::qSelect(static::budgetItemsQuery($budgetId, "E"), "categoryName", "categoryId");
            $budgetAttached = true;
        }

        return [
            'success' => true,
            'budgetAttached' => $budgetAttached,
            'incomeBudgetItems' => $incomeBudgetItems,
            'expenseBudgetItems' => $expenseBudgetItems,
        ];
    }

    /**
     * Get a budget status report by the statement id
     *
     * @param $statementId
     * @return array
     */
    public static function getBudgetStatus($statementId)
    {
        $statement = StatementModel::findOne($statementId);
        Helper::nullCheck($statement, "Statement Not Found!");
        $budgetId = $statement['budget'];

        $incomeReport = static::prepReportItems($budgetId, $statementId, "I");
        $expenseReport = static::prepReportItems($budgetId, $statementId, "E");

        return [
            "success" => true,
            "incomeReport" => $incomeReport,
            "expenseReport" => $expenseReport,
        ];
    }

    /**
     * Reusable data gathering function for a budget report
     *
     * @param $budgetId
     * @param $statementId
     * @param $categoryType
     * @return array
     */
    private static function prepReportItems($budgetId, $statementId, $categoryType)
    {

        $items = static::budgetItemsQuery($budgetId, $categoryType);
        $report = [];
        $index = 0;
        foreach($items as $item) {

            $categorySumQuery = Helper::query()
                ->from("{{%transactions}}")
                ->where("category = " . intval($item['categoryId']))
                ->andWhere("statement = $statementId");
            $categoryTotal = number_format(abs($categorySumQuery->sum("amount")),"2",".","");


            $categoryTransactions = Helper::query()
                ->select([
                    "*",
                    "t.id AS transactionId",
                    "DATE_FORMAT(t.transactionDate, '%c/%e/%y') AS transactionDateFormatted"
                ])
                ->from("{{%transactions}} t")
                ->join("LEFT JOIN","{{%recipients}} r ON r.id = t.recipient")
                ->join("LEFT JOIN", "{{%payers}} p ON p.id = t.payer")
                ->where("category = " . intval($item['categoryId']))
                ->andWhere("statement = $statementId")
                ->orderBy("t.transactionDate ASC")
                ->all();

            $categoryInfo = [
                "categoryId" => $item['categoryId'],
                "categoryName" => $item['categoryName'],
                "budgetedAmount" => $item['amount'],
                "transactionTotal" => $categoryTotal,
            ];

            $report[$index]["categoryInfo"] = $categoryInfo;
            $report[$index]["transactions"] = $categoryTransactions;
            $report[$index]['icon'] = "keyboard_arrow_down";
            $index++;
        }

        return $report;

    }

}