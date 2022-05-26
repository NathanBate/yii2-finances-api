<?php

namespace app\controllers\console;

use BaseApi;
use baseapi\console\Controller;
use yii\console\ExitCode;
use app\models\TransactionModel;
use app\helpers\BaseApiHelper as Helper;

class MaintenanceController extends Controller
{

    public $defaultAction = 'info';

    /**
     * Information on the Maintenance Console Section.
     *
     * @return int
     */
    public function actionInfo()
    {
        BaseApi::_console("Some of these methods will only be used once and then commented out.");
        return ExitCode::OK;
    }



    /**
     * In some mysql environments, timestamp value 00:00:00 is not allowed.
     *
     * @return int
     */
    public function actionFixZeroTimestamps()
    {
        BaseApi::_console("Getting All Timestamps");

        $transactions = Helper::query()
            ->select([
                "*",
                "t.id AS transactionId",
                "DATE_FORMAT(t.transactionDate, '%c/%e/%y') AS transactionDateFormatted",
                "FORMAT(t.total,2) AS total"
            ])
            ->from("{{%transactions}} t")
            ->all();

        BaseApi::_console("Iterating through transactions and updating.");

        foreach ($transactions as $t) {
            BaseApi::_console("Transaction Id is " . $t['id']);
            $date = explode(" ", $t['transactionDate']);
            $timestamp = explode(":", $date[1]);
            //BaseApi::_console("Hour is " . $timestamp[0]);
            //BaseApi::_console("Minute is " . $timestamp[1]);
            //BaseApi::_console("Second is " . $timestamp[2]);
            $newTimeStamp = $date[0] . " 01:" . $timestamp[1] . ":" . $timestamp[2];
            BaseApi::_console("New Timestamp is " . $newTimeStamp);


            BaseApi::_console("Udating Transaction");
            $currentTransaction = TransactionModel::findOne($t['id']);
            $currentTransaction->transactionDate = $newTimeStamp;
            $currentTransaction->save();
        }

        BaseApi::_console("Updates Done");

        return ExitCode::OK;

    }

}
