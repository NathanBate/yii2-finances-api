<?php

namespace app\controllers\web;

use BaseApi;

use baseapi\controllers\BaseController;

use app\models\TransactionModel;
use app\models\StatementModel;

use app\helpers\BaseApiHelper as Helper;

class StatementController extends BaseController
{

    /**
     * Get's the specified statement
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function actionView($id=-1)
    {
        Helper::checkParam($id, -1);
        return StatementModel::viewStatement($id);
    }

    /**
     * Add a Bank Account
     *
     * @return bool[]
     */
    public function actionAdd()
    {
        Helper::allowAdminOnly();

        $account = new Account();
        if ($account->load(BaseApi::$app->request->post(), '') && $account->add()) {
            return ["success" => true];
        } else {
            return ["success" => false];
        }
    }

    /**
     * Updates the Posted Statement.
     *
     * @return array
     */
    public function actionUpdate()
    {
        return StatementModel::updateStatement();
    }

    /**
     * Detaches the budget from the specified statement
     *
     * @param $id
     * @return array
     */
    public function actionDetachBudget($id)
    {
        Helper::checkParam($id, -1);
        return StatementModel::detachBudget($id);
    }

}