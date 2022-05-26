<?php

namespace app\controllers\web;

use app\models\BudgetCategoriesModel;
use BaseApi;
use yii\helpers\ArrayHelper;
use app\models\BudgetModel;
use baseapi\controllers\BaseController; // use this if you want to disable web viewing
use app\helpers\BaseApiHelper as Helper;

class BudgetController extends BaseController
{

    /**
     * Gets a list of budgets for the specified account
     *
     * @return array
     */
    public function actionIndex()
    {
        return BudgetModel::getBudgets();
    }

    /**
     * Adds a budget to the specified account
     *
     * @return array
     */
    public function actionAdd()
    {
        $budget = new BudgetModel();
        return Helper::loadAndAddPost($budget);
    }

    /**
     * Gets the specified budget
     *
     * @param int $id
     * @return array
     */
    public function actionGetBudget($id = -1)
    {
        Helper::checkParam($id, -1);
        return BudgetModel::getBudget($id);
    }

    /**
     * Adds the posted budget category
     *
     * @return array
     */
    public function actionAddBudgetCategory()
    {
        $budgetCategory = new BudgetCategoriesModel();
        return Helper::loadAndAddPost($budgetCategory);
    }

    /**
     * Updates the title of the specified budget
     *
     * @return array
     */
    public function actionUpdateTitle()
    {
        return BudgetModel::updateTitle();
    }

    /**
     * Adds a Budget Item to the Budget
     *
     * @return array
     */
    public function actionAddBudgetItem()
    {
        return BudgetModel::addBudgetItem();
    }

    /**
     * Gets the specified budget item
     *
     * @param int $id
     * @return array
     */
    public function actionGetBudgetItem($id = -1)
    {
        Helper::checkParam($id, -1);
        return BudgetModel::getBudgetItem($id);
    }

    /**
     * Deletes the specified budget item
     *
     * @param $id
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteBudgetItem($id = -1)
    {
        Helper::checkParam($id, -1);
        return BudgetModel::deleteBudgetItem($id);
    }

    /**
     * Updates the posted budget item
     *
     * @return array
     */
    public function actionUpdateBudgetItem()
    {
        return BudgetModel::updateBudgetItem();
    }

    /**
     * Archives the specified budget
     *
     * @param int $id
     * @return array
     */
    public function actionArchive($id = -1)
    {
        Helper::checkParam($id, -1);
        return BudgetModel::archiveBudget($id);
    }

    /**
     * Get the budget info by statement id
     *
     * @param $id
     * @return array
     */
    public function actionGetBudgetInfoByStatementId($id)
    {
        Helper::checkParam($id, -1);
        return BudgetModel::getBudgetInfoByStatementId($id);
    }


    /**
     * Get a budget status report by the statement id
     *
     * @param int $id
     * @return array
     */
    public function actionGetStatus($id = -1)
    {
        Helper::checkParam($id, -1);
        return BudgetModel::getBudgetStatus($id);
    }

}