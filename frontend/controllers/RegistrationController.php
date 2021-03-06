<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\frontend\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\widgets\ActiveForm;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yuncms\user\UserTrait;
use yuncms\user\models\User;
use yuncms\user\models\UserSocialAccount;

use yuncms\user\frontend\models\ResendForm;
use yuncms\user\frontend\models\RegistrationForm;



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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'minLength' => 4,
                'maxLength' => 5,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['register', 'captcha'],
                        'roles' => ['?']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['register', 'confirm', 'connect', 'resend'],
                        'roles' => ['?', '@']
                    ]
                ]
            ]
        ];
    }

    /**
     * Displays the registration page.
     * After successful registration if enableConfirmation is enabled shows info message otherwise redirects to home page.
     *
     * @return string|array
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'You have already registered.'));
            return $this->goBack();
        }
        if (!$this->getSetting('enableRegistration')) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'The system has closed the new user registration.'));
            return $this->goBack();
        }
        /** @var RegistrationForm $model */
        $model = new RegistrationForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            return $this->redirect(['/user/settings/profile']);
        }
        return $this->render('register', [
            'model' => $model,
            'enableRegistrationCaptcha' => $this->getSetting('enableRegistrationCaptcha'),
            'enableGeneratingPassword'=>$this->getSetting('enableGeneratingPassword'),
        ]);
    }

    /**
     * Displays page where user can create new account that will be connected to social account.
     *
     * @param string $code
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionConnect($code)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/user/settings/networks']);
        }

        $account = UserSocialAccount::find()->byCode($code)->one();

        if ($account === null || $account->getIsConnected()) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = Yii::createObject([
            'class' => User::className(),
            'scenario' => User::SCENARIO_CONNECT,
            'nickname' => $account->username,
            'email' => $account->email,
        ]);

        if ($user->load(Yii::$app->request->post()) && $user->create()) {
            $account->connect($user);
            Yii::$app->user->login($user, $this->getSetting('rememberFor'));
            return $this->goBack();
        }

        return $this->render('connect', [
            'model' => $user,
        ]);
    }

    /**
     * Confirms user's account. If confirmation was successful logs the user and shows success message. Otherwise
     * shows error message.
     *
     * @param integer $id
     * @param string $code
     *
     * @return string
     */
    public function actionConfirm($id, $code)
    {
        $user = User::findOne($id);
        if ($user === null || $this->getSetting('enableConfirmation') == false) {
            return $this->goBack();
        }
        $user->attemptConfirmation($code);
        return $this->redirect(['/user/settings/profile']);
    }

    /**
     * Displays page where user can request new confirmation token. If resending was successful, displays message.
     *
     * @return string|array
     */
    public function actionResend()
    {
        if ($this->getSetting('enableConfirmation') == false) {
            return $this->goBack();
        }
        /** @var ResendForm $model */
        $model = new ResendForm();
        if (Yii::$app->request->getIsAjax() && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->resend()) {
            return $this->redirect(['/user/settings/profile']);
        }
        return $this->render('resend', ['model' => $model]);
    }
}
