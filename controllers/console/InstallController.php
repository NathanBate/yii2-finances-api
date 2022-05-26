<?php

namespace app\controllers\console;

use BaseApi;
use baseapi\console\controllers\InstallController as BaseInstall;
use app\migrations\Install;
use yii\console\ExitCode;

class InstallController extends BaseInstall
{
    /**
     * Installs this API
     * @param bool $bUseBaseInstallMigration
     *
     * @return int
     */
    public function actionApi($bUseBaseInstallMigration=false)
    {
        /**
         * Run parent action, but don't use the parent install migration
         */
        parent::actionApi($bUseBaseInstallMigration);

        /**
         * Run our own install migration for this app so we can install our own tables
         */
        $installMigration = new Install([
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'password' => $this->password,
            'admin' => 'S',
            'email' => $this->emailAddress,
        ]);
        $result = $installMigration->safeUp();

        if ($result) {
            BaseApi::_console("Installation Done! ");
        } else {
            BaseApi::_console("Installation Failed!");
        }

        /**
         * Run Migrations
         */
        BaseApi::_console("Running Migrations... ");
        $migrationResult = BaseApi::$app->runAction('migrate', ['migrationPath' => '@app/migrations/', 'interactive' => false]);

        return ExitCode::OK;
    }

    /**
     * This is only available in dev mode
     *
     * @return int
     */
    public function actionUninstall() {
        $uninstallMigration = new Install();

        $result = $uninstallMigration->safeDown();

        if ($result) {
            BaseApi::_console("Removal Done! ");
        } else {
            BaseApi::_console("Removal Failed!");
        }

        return ExitCode::OK;
    }

}