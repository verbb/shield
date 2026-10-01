<?php
namespace verbb\shield\controllers;

use verbb\shield\Shield;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class LogsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requireCpRequest();
        $this->requirePermission('accessPlugin-shield');

        return true;
    }

    public function actionIndex(): Response
    {
        $logs = Shield::$plugin->getLogs()->getAllLogs();

        return $this->renderTemplate('shield/logs', [
            'logs' => $logs,
            'canManageLogs' => Craft::$app->getUser()->checkPermission(Shield::PERMISSION_MANAGE_LOGS),
        ]);
    }

    public function actionDelete(): void
    {
        $this->requirePostRequest();
        $this->requirePermission(Shield::PERMISSION_MANAGE_LOGS);

        $id = Craft::$app->getRequest()->getParam('id');

        if (!$id || !(Shield::$plugin->getLogs()->getLogById($id))) {
            Craft::$app->getSession()->setError('Could not find log to delete.');
        }

        if (!Shield::$plugin->getLogs()->deleteLogById($id)) {
            Craft::$app->getSession()->setError('Could not delete log.');
        }

        Craft::$app->getSession()->setNotice('Deleted successfully.');
    }

    public function actionClear(): void
    {
        $this->requirePostRequest();
        $this->requirePermission(Shield::PERMISSION_MANAGE_LOGS);

        if (!Shield::$plugin->getLogs()->deleteAllLogs()) {
            Craft::$app->getSession()->setError('Could not clear all logs.');
        }

        Craft::$app->getSession()->setNotice('Cleared logs successfully.');
    }
}
