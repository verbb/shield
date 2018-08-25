<?php
namespace selvinortiz\shield\controllers;

use Craft;
use craft\web\Controller;

use function selvinortiz\shield\shield;

class SubmissionsController extends Controller
{
    protected $allowAnonymous = true;

    public function actionHandle()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        $data = [
            'email' => $request->post('email'),
            'author' => $request->post('name'),
            'content' => $request->post('comments'),
        ];

        $spam = shield()->service->detectSpam($data);

        $response->format = 'json';

        return $this->asJson(compact('spam'));
    }
}
