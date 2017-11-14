<?php

namespace yuncms\user\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\authclient\ClientInterface as BaseClientInterface;
use yuncms\user\clients\ClientInterface;

/**
 * This is the model class for table "{{%user_social_account}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $username
 * @property string $email
 * @property string $provider
 * @property string $client_id
 * @property string $code
 * @property integer $created_at
 * @property string $data
 *
 * @property User $user
 *
 * @property-read bool isAuthor 是否是作者
 * @property-read boolean $isDraft 是否草稿
 * @property-read boolean $isPublished 是否发布
 */
class UserSocialAccount extends ActiveRecord
{
    /**
     * @var
     */
    private $_data;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_social_account}}';
    }

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::className(),
            'attributes' => [
                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('user', 'ID'),
            'user_id' => Yii::t('user', 'Uer ID'),
            'username' => Yii::t('user', 'Username'),
            'email' => Yii::t('user', 'EMail'),
            'provider' => Yii::t('user', 'Provider'),
            'client_id' => Yii::t('user', 'Client Id'),
            'code' => Yii::t('user', 'Code'),
            'created_at' => Yii::t('user', 'Created At'),
            'data' => Yii::t('user', 'Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return boolean Whether this social account is connected to user.
     */
    public function getIsConnected()
    {
        return $this->user_id != null;
    }

    /**
     * Returns connect url.
     * @return string
     */
    public function getConnectUrl()
    {
        $code = Yii::$app->security->generateRandomString();
        $this->updateAttributes(['code' => md5($code)]);

        return Url::to(['/user/registration/connect', 'code' => $code]);
    }

    public function connect(User $user)
    {
        return $this->updateAttributes(['username' => null, 'email' => null, 'code' => null, 'user_id' => $user->id]);
    }

    /**
     * @return mixed Json decoded properties.
     */
    public function getDecodedData()
    {
        if ($this->_data == null) {
            $this->_data = Json::decode($this->data);
        }
        return $this->_data;
    }

    public static function create(BaseClientInterface $client)
    {
        /** @var UserSocialAccount $account */
        $account = Yii::createObject([
            'class' => static::className(),
            'provider' => $client->getId(),
            'client_id' => $client->getUserAttributes()['id'],
            'data' => json_encode($client->getUserAttributes())
        ]);

        if ($client instanceof ClientInterface) {
            $account->setAttributes(['username' => $client->getUsername(), 'email' => $client->getEmail()], false);
        }

        if (($user = static::fetchUser($account)) instanceof User) {
            $account->user_id = $user->id;
        }

        $account->save(false);

        return $account;
    }


    /**
     * Tries to find an account and then connect that account with current user.
     *
     * @param BaseClientInterface $client
     */
    public static function connectWithUser(BaseClientInterface $client)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Something went wrong'));
            return;
        }

        $account = static::fetchAccount($client);

        if ($account->user === null) {
            $account->link('user', Yii::$app->user->identity);
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your account has been connected'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'This account has already been connected to another user'));
        }
    }

    /**
     * Tries to find account, otherwise creates new account.
     *
     * @param BaseClientInterface $client
     *
     * @return UserSocialAccount
     * @throws \yii\base\InvalidConfigException
     */
    protected static function fetchAccount(BaseClientInterface $client)
    {
        $account = UserSocialAccount::find()->byClient($client)->one();
        if (null === $account) {
            $account = Yii::createObject(['class' => static::className(), 'provider' => $client->getId(), 'client_id' => $client->getUserAttributes()['id'], 'data' => json_encode($client->getUserAttributes())]);
            $account->save(false);
        }

        return $account;
    }

    /**
     * Tries to find user or create a new one.
     *
     * @param UserSocialAccount $account
     *
     * @return User|boolean False when can't create user.
     */
    protected static function fetchUser(UserSocialAccount $account)
    {
        $user = User::findByEmail($account->email);

        if (null !== $user) {
            return $user;
        }

        /** @var \yuncms\user\models\User $user */
        $user = Yii::createObject([
            'class' => User::className(),
            'scenario' => User::SCENARIO_CONNECT,
            'nickname' => $account->username,
            'email' => $account->email
        ]);

        if (!$user->validate(['email'])) {
            $account->email = null;
        }

        if (!$user->validate(['nickname'])) {
            $account->username = null;
        }

        return $user->create() ? $user : false;
    }

    /**
     * @inheritdoc
     * @return UserSocialAccountQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserSocialAccountQuery(get_called_class());
    }

//    public function afterFind()
//    {
//        parent::afterFind();
//        // ...custom code here...
//    }

    /**
     * @inheritdoc
     */
//    public function beforeSave($insert)
//    {
//        if (!parent::beforeSave($insert)) {
//            return false;
//        }
//
//        // ...custom code here...
//        return true;
//    }

    /**
     * @inheritdoc
     */
//    public function afterSave($insert, $changedAttributes)
//    {
//        parent::afterSave($insert, $changedAttributes);
//        Yii::$app->queue->push(new ScanTextJob([
//            'modelId' => $this->getPrimaryKey(),
//            'modelClass' => get_class($this),
//            'scenario' => $this->isNewRecord ? 'new' : 'edit',
//            'category'=>'',
//        ]));
//        // ...custom code here...
//    }

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
