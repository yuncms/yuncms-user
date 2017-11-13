<?php

namespace yuncms\user\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Application as WebApplication;
use yii\web\IdentityInterface;
use yuncms\core\helpers\PasswordHelper;
use yuncms\tag\models\Tag;
use yuncms\user\frontend\assets\UserAsset;
use yuncms\user\UserTrait;

/**
 * This is the model class for table "{{%user}}".
 *
 * Database fields:
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $mobile
 * @property string $nickname
 * @property string $auth_key
 * @property string $password_hash
 * @property string $access_token
 * @property integer $avatar
 * @property string $unconfirmed_email
 * @property string $unconfirmed_mobile
 * @property string $registration_ip
 * @property integer $flags
 * @property integer $email_confirmed_at
 * @property integer $mobile_confirmed_at
 * @property integer $blocked_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * Defined relations:
 * @property UserExtra $extra
 * @property UserLoginHistory[] $userLoginHistories
 * @property UserProfile $profile
 * @property UserSocialAccount[] $socialAccounts
 * @property Tag[] $tags
 * @property UserToken[] $userTokens
 *
 * @property-read boolean $isBlocked 账户是否锁定
 * @property-read bool $isMobileConfirmed 是否已经手机激活
 * @property-read bool $isEmailConfirmed 是否已经邮箱激活
 * @property-read bool $isAvatar 是否有头像
 */
class User extends ActiveRecord implements IdentityInterface
{
    use UserTrait;

    //事件定义
    const BEFORE_CREATE = 'beforeCreate';
    const AFTER_CREATE = 'afterCreate';
    const BEFORE_REGISTER = 'beforeRegister';
    const AFTER_REGISTER = 'afterRegister';

    //场景定义
    const SCENARIO_EMAIL_REGISTER = 'email_create';//创建
    const SCENARIO_MOBILE_REGISTER = 'mobile_create';//更新
    const SCENARIO_SETTINGS = 'settings';//更新

    //头像
    const AVATAR_BIG = 'big';
    const AVATAR_MIDDLE = 'middle';
    const AVATAR_SMALL = 'small';

    /**
     * @var string Plain password. Used for model validation.
     */
    public $password;

    /**
     * @var UserProfile|null
     */
    private $_profile;

    /** @var  UserExtra|null */
    private $_extra;

    /**
     * @var string Default username regexp
     */
    public static $usernameRegexp = '/^[-a-zA-Z0-9_]+$/u';

    /**
     * @var string Default nickname regexp
     */
    public static $nicknameRegexp = '/^[-a-zA-Z0-9_\x{4e00}-\x{9fa5}\.@]+$/u';

    /**
     * @var string Default mobile regexp
     */
    public static $mobileRegexp = '/^13[\d]{9}$|^15[\d]{9}$|^17[\d]{9}$|^18[\d]{9}$/';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior'
            ],
            'taggable' => [
                'class' => 'creocoder\taggable\TaggableBehavior',
                'tagValuesAsArray' => true,
                'tagRelation' => 'tags',
                'tagValueAttribute' => 'id',
                'tagFrequencyAttribute' => 'frequency',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            static::SCENARIO_EMAIL_REGISTER => ['nickname', 'email', 'password'],
            static::SCENARIO_MOBILE_REGISTER => ['mobile', 'password'],
            static::SCENARIO_SETTINGS => ['username', 'email', 'password'],
        ]);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username rules
            'usernameMatch' => ['username', 'match', 'pattern' => static::$usernameRegexp],
            'usernameLength' => ['username', 'string', 'min' => 3, 'max' => 50],
            'usernameUnique' => ['username', 'unique', 'message' => Yii::t('user', 'This username has already been taken')],
            'usernameTrim' => ['username', 'trim'],

            // nickname rules
            'nicknameRequired' => ['nickname', 'required', 'on' => [self::SCENARIO_EMAIL_REGISTER]],
            'nicknameMatch' => ['nickname', 'match', 'pattern' => static::$nicknameRegexp],
            'nicknameLength' => ['nickname', 'string', 'min' => 3, 'max' => 255],
            'nicknameUnique' => ['nickname', 'unique', 'message' => Yii::t('user', 'This nickname has already been taken')],
            'nicknameTrim' => ['nickname', 'trim'],

            // email rules
            'emailRequired' => ['email', 'required', 'on' => [self::SCENARIO_EMAIL_REGISTER]],
            'emailPattern' => ['email', 'email', 'checkDNS' => true],
            'emailLength' => ['email', 'string', 'max' => 255],
            'emailUnique' => ['email', 'unique', 'message' => Yii::t('user', 'This email address has already been taken')],
            'emailTrim' => ['email', 'trim'],
            'emailDefault' => ['email', 'default', 'value' => null],

            //mobile rules
            'mobileRequired' => ['mobile', 'required', 'on' => [self::SCENARIO_MOBILE_REGISTER]],
            'mobilePattern' => ['mobile', 'match', 'pattern' => static::$mobileRegexp],
            'mobileLength' => ['mobile', 'string', 'max' => 11],
            'mobileUnique' => ['mobile', 'unique', 'message' => Yii::t('user', 'This phone has already been taken')],
            'mobileDefault' => ['mobile', 'default', 'value' => null],

            // password rules
            'passwordRequired' => ['password', 'required', 'on' => [self::SCENARIO_EMAIL_REGISTER]],
            'passwordLength' => ['password', 'string', 'min' => 6],

            // tags rules
            'tags' => ['tagValues', 'safe'],


            [['flags', 'email_confirmed_at', 'mobile_confirmed_at', 'blocked_at'], 'integer'],
            [['registration_ip'], 'string', 'max' => 255],
            [['mobile', 'unconfirmed_mobile'], 'string', 'max' => 11],
            [['access_token'], 'string', 'max' => 100],
            [['unconfirmed_email'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('user', 'ID'),
            'username' => Yii::t('user', 'Username'),
            'email' => Yii::t('user', 'Email'),
            'mobile' => Yii::t('user', 'Mobile'),
            'nickname' => Yii::t('user', 'Nickname'),
            'auth_key' => Yii::t('user', 'Auth Key'),
            'password_hash' => Yii::t('user', 'Password Hash'),
            'access_token' => Yii::t('user', 'Access Token'),
            'avatar' => Yii::t('user', 'Avatar'),
            'unconfirmed_email' => Yii::t('user', 'Unconfirmed Email'),
            'unconfirmed_mobile' => Yii::t('user', 'Unconfirmed Mobile'),
            'registration_ip' => Yii::t('user', 'Registration Ip'),
            'flags' => Yii::t('user', 'Flags'),
            'email_confirmed_at' => Yii::t('user', 'Email Confirmed At'),
            'mobile_confirmed_at' => Yii::t('user', 'Mobile Confirmed At'),
            'blocked_at' => Yii::t('user', 'Blocked At'),
            'created_at' => Yii::t('user', 'Created At'),
            'updated_at' => Yii::t('user', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserExtra()
    {
        return $this->hasOne(UserExtra::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserLoginHistories()
    {
        return $this->hasMany(UserLoginHistory::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
    }

    /**
     * 返回所有已经连接的社交媒体账户
     * @return UserSocialAccount[] Connected accounts ($provider => $account)
     */
    public function getUserSocialAccounts()
    {
        $connected = [];
        /** @var UserSocialAccount[] $accounts */
        $accounts = $this->hasMany(UserSocialAccount::className(), ['user_id' => 'id'])->all();
        /**
         * @var UserSocialAccount $account
         */
        foreach ($accounts as $account) {
            $connected[$account->provider] = $account;
        }

        return $connected;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('{{%user_tag}}', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTokens()
    {
        return $this->hasMany(UserToken::className(), ['user_id' => 'id']);
    }

    /**
     * 获取头像Url
     * @param string $size
     * @return string
     */
    public function getAvatar($size = self::AVATAR_MIDDLE)
    {
        $size = in_array($size, ['big', 'middle', 'small']) ? $size : 'big';
        if ($this->getIsAvatar()) {
            $avatarFileName = "_avatar_$size.jpg";
            return $this->getAvatarUrl($this->id) . $avatarFileName;
        } else {
            switch ($size) {
                case 'big':
                    $avatarUrl = '/img/no_avatar_big.gif';
                    break;
                case 'middle':
                    $avatarUrl = '/img/no_avatar_middle.gif';
                    break;
                case 'small':
                    $avatarUrl = '/img/no_avatar_small.gif';
                    break;
                default:
                    $avatarUrl = '/img/no_avatar_big.gif';
            }
            if (Yii::getAlias('@webroot', false)) {
                $baseUrl = UserAsset::register(Yii::$app->view)->baseUrl;
                return Url::to($baseUrl . $avatarUrl, true);
            } else {
                return '';
            }
        }
    }

    /**
     * 设置用户资料
     * @param UserProfile $profile
     */
    public function setProfile(UserProfile $profile)
    {
        $this->_profile = $profile;
    }

    /**
     * 设置用户延伸资料
     * @param UserExtra $extra
     */
    public function setExtra($extra)
    {
        $this->_extra = $extra;
    }

    /**
     * @inheritdoc
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * 通过登陆邮箱或手机号获取用户
     * @param string $emailOrMobile
     * @return User|null
     */
    public static function findByEmailOrMobile($emailOrMobile)
    {
        if (filter_var($emailOrMobile, FILTER_VALIDATE_EMAIL)) {
            return static::findByEmail($emailOrMobile);
        } else if (preg_match(self::$mobileRegexp, $emailOrMobile)) {
            return static::findByMobile($emailOrMobile);
        }
        return null;
    }

    /**
     * 通过邮箱获取用户
     * @param string $email 邮箱
     * @return null|static
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * 通过手机号获取用户
     * @param string $mobile
     * @return static
     */
    public static function findByMobile($mobile)
    {
        return static::findOne(['mobile' => $mobile]);
    }

    /**
     * 通过用户名获取用户
     * @param string $username 用户标识
     * @return null|static
     */
    public static function findModelByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 获取auth_key
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * 验证密码
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * 验证AuthKey
     * @param string $authKey
     * @return boolean
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 创建 "记住我" 身份验证Key
     * @return void
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * 创建 "记住我" 身份验证Key
     * @return void
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }

    /**
     * 随机生成一个用户名
     */
    public function generateUsername()
    {
        if ($this->email) {
            $this->username = explode('@', $this->email)[0];
            if ($this->validate(['username'])) {
                return $this->username;
            }
        } else if ($this->nickname) {
            $this->username = Inflector::slug($this->nickname, '');
            if ($this->validate(['username'])) {
                return $this->username;
            }
        }
        // generate name like "user1", "user2", etc...
        while (!$this->validate(['username'])) {
            $row = (new Query())->from('{{%user}}')->select('MAX(id) as id')->one();
            $this->username = 'user' . ++$row['id'];
        }
        return $this->username;
    }

    /**
     * 重置密码
     *
     * @param string $password
     *
     * @return boolean
     */
    public function resetPassword($password)
    {
        return (bool)$this->updateAttributes(['password_hash' => PasswordHelper::hash($password)]);
    }

    /**
     * 锁定用户
     * @return boolean
     */
    public function block()
    {
        return (bool)$this->updateAttributes(['blocked_at' => time(), 'auth_key' => Yii::$app->security->generateRandomString()]);
    }

    /**
     * 解除用户锁定
     * @return boolean
     */
    public function unblock()
    {
        return (bool)$this->updateAttributes(['blocked_at' => null]);
    }

    /**
     * 返回用户是否已经锁定
     * @return boolean Whether the user is blocked or not.
     */
    public function getIsBlocked()
    {
        return $this->blocked_at != null;
    }

    /**
     * 返回用户是否有头像
     * @return boolean Whether the user is blocked or not.
     */
    public function getIsAvatar()
    {
        return $this->avatar != 0;
    }

    /**
     * 返回用户邮箱是否已经激活
     * @return boolean Whether the user is confirmed or not.
     */
    public function getIsEmailConfirmed()
    {
        return $this->email_confirmed_at != null;
    }

    /**
     * 返回用户手机是否已经激活
     * @return boolean Whether the user is confirmed or not.
     */
    public function getIsMobileConfirmed()
    {
        return $this->mobile_confirmed_at != null;
    }

    /**
     * 此方法用于注册新用户帐户。 如果 enableConfirmation 设置为true，则此方法
     * 将生成新的确认令牌，并使用邮件发送给用户。
     *
     * @return boolean
     */
    public function register()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }
        $this->password = $this->getSetting('enableGeneratingPassword') ? PasswordHelper::generate(8) : $this->password;
        if ($this->scenario == self::SCENARIO_EMAIL_REGISTER) {
            $this->email_confirmed_at = $this->getSetting('enableConfirmation') ? null : time();
        }
        $this->trigger(self::BEFORE_REGISTER);
        if (!$this->save()) {
            return false;
        }
        if ($this->getSetting('enableConfirmation') && !empty($this->email)) {
            /** @var UserToken $token */
            $token = new UserToken(['type' => UserToken::TYPE_CONFIRMATION]);
            $token->link('user', $this);
            $this->sendMessage($this->email, Yii::t('user', 'Welcome to {0}', Yii::$app->name), 'welcome', ['user' => $this, 'token' => isset($token) ? $token : null, 'module' => $this->module, 'showPassword' => false]);
        } else {
            Yii::$app->user->login($this, $this->getSetting('rememberFor'));
        }
        $this->trigger(self::AFTER_REGISTER);
        return true;
    }

//    public function afterFind()
//    {
//        parent::afterFind();
//        // ...custom code here...
//    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->generateAccessToken();
            $this->generateAuthKey();
            if (Yii::$app instanceof WebApplication) {
                $this->registration_ip = Yii::$app->request->getUserIP();
            }
            if ($this->username == null) {
                $this->username = $this->generateUsername();
            }
        }
        if (!empty($this->password)) {
            $this->password_hash = PasswordHelper::hash($this->password);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if ($this->_profile == null) {
                $this->_profile = new UserProfile();
            }
            $this->_profile->link('user', $this);

            if ($this->_extra == null) {
                $this->_extra = new UserExtra();
            }
            $this->_extra->link('user', $this);
        }
    }

    /**
     * @inheritdoc
     */
//    public function beforeDelete()
//    {
//        if (!parent::beforeDelete()) {
//            return false;
//        }
//        // ...custom code here...
//        return true;
//    }

    /**
     * @inheritdoc
     */
//    public function afterDelete()
//    {
//        parent::afterDelete();
//
//        // ...custom code here...
//    }
}
