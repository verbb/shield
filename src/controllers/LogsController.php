<?php

namespace selvinortiz\shield\controllers;

use Craft;
use craft\web\Controller;

use function selvinortiz\shield\shield;
use selvinortiz\shield\records\LogRecord;

class LogsController extends Controller
{
    public function actionDeleteOne()
    {
        $this->requirePostRequest();

        $id = Craft::$app->request->post('id');

        if (!$id || !($log = LogRecord::find()->where(['id' => $id])->one()))
        {
            return Craft::$app->session->setError('Could not find log to delete.');
        }

        if (!$log->delete())
        {
            return Craft::$app->session->setError('Could not delete log.');
        }

        return Craft::$app->session->setNotice('Deleted successfully.');
    }

    public function actionDeleteAll()
    {
        $this->requirePostRequest();

        $confirmation = Craft::$app->request->post('confirmation');

        if (!$confirmation)
        {
            return Craft::$app->session->setError('Confirm that you actually want to delete everything.');
        }

        if (!shield()->logs->delete())
        {
            return Craft::$app->session->setError('Could not delete any logs.');
        }

        return Craft::$app->session->setNotice('Deleted logs successfully.');
    }
}
