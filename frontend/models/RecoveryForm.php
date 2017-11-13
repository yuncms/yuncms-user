<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\frontend\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yuncms\user\models\User;
use yuncms\user\models\UserToken;
use yuncms\user\UserTrait;

/**
 * Model for collecting data on password recovery.
 *
 * @property \yuncms\user\Module $module
 */
class RecoveryForm extends Model
{
    use UserTrait;

    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $password;

    /**
     * @var User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'password' => Yii::t('user', 'Password')
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, ['request' => ['email'], 'reset' => ['password']]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'emailTrim' => ['email', 'filter', 'filter' => 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern' => ['email', 'email'],
            'emailExist' => ['email', 'exist', 'targetClass' => User::className(), 'message' => Yii::t('user', 'There is no user with this email address')],
            'emailUnconfirmed' => ['email', function ($attribute) {
                $this->user = User::findByEmail($this->email);
                if ($this->user !== null && $this->getSetting('enableConfirmation') && !$this->user->isEmailConfirmed) {
                    $this->addError($attribute, Yii::t('user', 'You need to confirm your email address.'));
                }
            }],
            'passwordRequired' => ['password', 'required'],
            'passwordLength' => ['password', 'string', 'min' => 6]
        ];
    }

    /**
     * Sends recovery message.
     *
     * @return boolean
     */
    public function sendRecoveryMessage()
    {
        if ($this->validate()) {
            /** @var UserToken $token */
            $token = new UserToken([ 'user_id' => $this->user->id, 'type' => UserToken::TYPE_RECOVERY]);
            $token->save(false);
            $this->sendMessage($this->user->email,Yii::t('user', 'Complete password reset on {0}', Yii::$app->name),'recovery',['user' => $this->user, 'token' => $token]);
            Yii::$app->session->setFlash('info', Yii::t('user', 'An email has been sent with instructions for resetting your password'));
            return true;
        }
        return false;
    }

    /**
     * Resets user's password.
     *
     * @param UserToken $token
     *
     * @return boolean
     */
    public function resetPassword(UserToken $token)
    {
        if (!$this->validate() || $token->user === null) {
            return false;
        }
        if ($token->user->resetPassword($this->password)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your password has been changed successfully.'));
            $token->delete();
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred and your password has not been changed. Please try again later.'));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'recovery-form';
    }
}
