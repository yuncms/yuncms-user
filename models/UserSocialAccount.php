<?php

namespace yuncms\user\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

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
    public function rules()
    {
        return [
            [['user_id', 'created_at'], 'integer'],
            [['provider', 'client_id', 'created_at'], 'required'],
            [['data'], 'string'],
            [['username', 'email'], 'string', 'max' => 255],
            [['provider'], 'string', 'max' => 50],
            [['client_id'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 32],
            [['provider', 'client_id'], 'unique', 'targetAttribute' => ['provider', 'client_id']],
            [['code'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
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
