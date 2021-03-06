<?php
/**
 * User: xczizz
 * Date: 2018/9/4
 * Time: 14:08.
 */

namespace backend\controllers;

use yii\filters\VerbFilter;
use yii\web\Controller;

class UserTagController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('/tag/index');
    }
}
