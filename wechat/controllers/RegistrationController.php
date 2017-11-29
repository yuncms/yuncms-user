<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\wechat\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yuncms\user\UserTrait;
use yuncms\user\models\UserSocialAccount;
use yuncms\user\wechat\models\ConnectForm;

/**
 * RegistrationController is responsible for all registration process, which includes registration of a new account,
 * resending confirmation tokens, email confirmation and registration via social networks.
 *
 * @property \yuncms\user\Module $module
 *
 */
class RegistrationController extends Controller
{
    use UserTrait;

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [ 'connect'],
                        'roles' => ['?', '@']
                    ]
                ]
            ]
        ];
    }

    /**
     * 将微信用户连接到系统内用户
     *
     * @param string $code
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionConnect($code)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $account = UserSocialAccount::find()->byCode($code)->one();
        if ($account === null || $account->getIsConnected()) {
            throw new NotFoundHttpException();
        }
        $model = new ConnectForm(['socialAccount' => $account]);
        if ($model->load(Yii::$app->request->post()) && $model->connect()) {
            return $this->goBack(Yii::$app->getHomeUrl());
        }
        return $this->render('connect', [
            'model' => $model,
        ]);
    }
}
