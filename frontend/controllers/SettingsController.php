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
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yuncms\tag\models\Tag;
use yuncms\user\models\Settings;
use yuncms\user\models\User;
use yuncms\user\models\UserProfile;
use yuncms\user\models\UserSocialAccount;
use yuncms\user\frontend\models\AvatarForm;
use yuncms\user\frontend\models\SettingsForm;
use yuncms\user\UserTrait;

/**
 * SettingsController manages updating user settings (e.g. profile, email and password).
 *
 * @property \yuncms\user\Module $module
 */
class SettingsController extends Controller
{
    use UserTrait;

    /** @inheritdoc */
    public $defaultAction = 'profile';

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post'],
                    'follower-tag'=>['post']
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['profile', 'account', 'privacy', 'avatar', 'confirm', 'networks', 'disconnect','follower-tag'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Shows profile settings form.
     * @return array|string|Response
     */
    public function actionProfile()
    {
        $model = UserProfile::findOne(['user_id' => Yii::$app->user->identity->getId()]);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('user', 'Your profile has been updated'));
            return $this->refresh();
        }
        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * Show portrait setting form
     * @return \yii\web\Response|string
     */
    public function actionAvatar()
    {
        $model = new AvatarForm();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('user', 'Your avatar has been updated'));
        }
        return $this->render('avatar', [
            'model' => $model,
        ]);
    }

    /**
     * Displays page where user can update account settings (username, email or password).
     * @return array|string|Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = new SettingsForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your account details have been updated.'));
            return $this->refresh();
        }
        return $this->render('account', [
            'model' => $model,
        ]);
    }

    /**
     * 关注某tag
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionFollowerTag()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $tagId = Yii::$app->request->post('tag_id', null);
        if (($tag = Tag::findOne($tagId)) == null) {
            throw new NotFoundHttpException ();
        } else {
            /** @var \yuncms\user\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->hasTagValues($tag->id)) {
                $user->removeTagValues($tag->id);
                $user->save();
                return ['status' => 'unFollowed'];
            } else {
                $user->addTagValues($tag->id);
                $user->save();
                return ['status' => 'followed'];
            }
        }
    }

    /**
     * Attempts changing user's email address.
     *
     * @param integer $id
     * @param string $code
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionConfirm($id, $code)
    {
        $user = User::findOne($id);
        if ($user === null || $this->getSetting('emailChangeStrategy') == Settings::STRATEGY_INSECURE) {
            throw new NotFoundHttpException();
        }
        $user->attemptEmailChange($code);
        return $this->redirect(['account']);
    }

    /**
     * Displays list of connected network accounts.
     *
     * @return string
     */
    public function actionNetworks()
    {
        return $this->render('networks', [
            'user' => Yii::$app->user->identity,
        ]);
    }

    /**
     * Disconnects a network account from user.
     *
     * @param integer $id
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDisconnect($id)
    {
        $account = UserSocialAccount::find()->byId($id)->one();
        if ($account === null) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your account have been updated.'));
            return $this->redirect(['networks']);
        }
        if ($account->user_id != Yii::$app->user->id) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'You do not have the right to dismiss this social account.'));
            return $this->redirect(['networks']);
        }
        $account->delete();
        Yii::$app->session->setFlash('success', Yii::t('user', 'Your account have been updated.'));
        return $this->redirect(['networks']);
    }
}
