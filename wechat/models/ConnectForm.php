<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\wechat\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yuncms\user\jobs\SocialAvatarDownloadJob;
use yuncms\user\models\User;
use yuncms\user\models\UserSocialAccount;
use yuncms\core\helpers\PasswordHelper;
use yuncms\user\UserTrait;

/**
 * 注册或绑定用户
 */
class ConnectForm extends Model
{
    use UserTrait;

    /**
     * @var string User's email
     */
    public $email;

    /**
     * @var string User's plain password
     */
    public $password;

    /**
     * @var \yuncms\user\models\User
     */
    protected $user;

    /**
     * @var UserSocialAccount 社交账户实例
     */
    public $socialAccount;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'login' => Yii::t('user', 'Login'),
            'password' => Yii::t('user', 'Password'),
            'rememberMe' => Yii::t('user', 'Remember me next time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // email rules
            'emailRequired' => ['email', 'required'],
            'emailTrim' => ['email', 'filter', 'filter' => 'trim'],
            'emailPattern' => ['email', 'email'],

            'passwordRequired' => ['password', 'required'],
            'passwordLength' => ['password', 'string', 'min' => 6],
            'passwordTrim' => ['password', 'filter', 'filter' => 'trim'],
        ];
    }

    /**
     * Validates form and logs the user in.
     *
     * @return boolean whether the user is logged in successfully
     * @throws \yii\base\InvalidConfigException
     */
    public function connect()
    {
        if ($this->validate()) {
            $this->user = User::findByEmail($this->email);
            if ($this->user === null) {
                /** @var \yuncms\user\models\User $user */
                $this->user = Yii::createObject([
                    'class' => User::className(),
                    'scenario' => User::SCENARIO_CONNECT,
                    'nickname' => $this->socialAccount->username,
                ]);
                // generate nickname like "user1", "user2", etc...
                while (!$this->user->validate(['nickname'])) {
                    $row = (new Query())->from('{{%user}}')->select('MAX(id) as id')->one();
                    $user->nickname = $this->socialAccount->username . ++$row['id'];
                }
                if ($this->user->create()) {//新注册的用户 此处开始下载微信头像保存到本地
                    Yii::$app->queue->push(new SocialAvatarDownloadJob(['user_id'=>$user->id,'faceUrl'=>$this->socialAccount->decodedData['headimgurl']]));
                } else {
                    $this->addErrors($this->user->getErrors());
                    return false;
                }
            } else {
                if (!PasswordHelper::validate($this->password, $this->user->password_hash)) {
                    $this->addError('password', Yii::t('user', 'Invalid login or password'));
                    return false;
                }
                if ($this->user->getIsBlocked()) {
                    $this->addError('login', Yii::t('user', 'Your account has been blocked.'));
                    return false;
                }
            }
            $this->socialAccount->connect($this->user);
            return Yii::$app->user->login($this->user, $this->getSetting('rememberFor'));
        } else {
            return false;
        }
    }

    /**
     * 设置社交账户实例
     * @param UserSocialAccount $socialAccount
     */
    public function setSocialAccount(UserSocialAccount $socialAccount)
    {
        $this->socialAccount = $socialAccount;
    }
}
