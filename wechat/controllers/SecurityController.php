<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\wechat\controllers;


use Yii;

use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use xutl\wechat\oauth\OAuth;
use xutl\wechat\oauth\AuthAction;
use yuncms\user\models\UserSocialAccount;
use yuncms\user\Module;
use yuncms\user\models\User;
use yuncms\user\UserTrait;
use yuncms\user\wechat\models\ConnectForm;

/**
 * Controller that manages user authentication process.
 *
 * @property Module $module
 */
class SecurityController extends Controller
{
    use UserTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'connect'],
                        'roles' => ['?', '@']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@']
                    ],
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'login' => [
                'class' => AuthAction::className(),
                'successCallback' => [$this, 'authenticate']
            ]
        ];
    }

    /**
     * 退出用户后重定向到主页
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->getUser()->logout();
        Yii::$app->user->setReturnUrl(Yii::$app->request->getReferrer());
        return $this->goBack();
    }

    /**
     * 通过微信登录，如果用户不存在，将创建或绑定用户
     * @param OAuth $client
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function authenticate(OAuth $client)
    {
        $account = UserSocialAccount::find()->byClient($client)->one();
        if ($account === null) {
            $account = UserSocialAccount::create($client);
        }
        if ($account->user instanceof User) {
            if ($account->user->isBlocked) {
                Yii::$app->session->setFlash('danger', Yii::t('user', 'Your account has been blocked.'));
                $this->action->successUrl = Url::to(['/user/security/login']);
            } else {
                Yii::$app->user->login($account->user, $this->getSetting('rememberFor'));
                $this->action->successUrl = Yii::$app->getUser()->getReturnUrl();
            }
        } else {
            $this->action->successUrl = $account->getConnectUrl();
        }
    }


}
