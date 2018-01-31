<?php

namespace yuncms\user\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yuncms\user\UserTrait;

/**
 * This is the model class for table "{{%user_token}}".
 *
 * @property integer $user_id
 * @property string $code
 * @property integer $type
 * @property integer $created_at
 *
 * @property User $user
 *
 * @property-read bool isExpired 是否过期
 */
class UserToken extends ActiveRecord
{

    use UserTrait;

    const TYPE_CONFIRMATION = 0b0;
    const TYPE_RECOVERY = 0b1;
    const TYPE_CONFIRM_NEW_EMAIL = 0b10;
    const TYPE_CONFIRM_OLD_EMAIL = 0b11;
    const TYPE_CONFIRM_NEW_MOBILE = 0b100;
    const TYPE_CONFIRM_OLD_MOBILE = 0b101;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_token}}';
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['user_id', 'code', 'type'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('user', 'User ID'),
            'code' => Yii::t('user', 'Code'),
            'type' => Yii::t('user', 'Type'),
            'created_at' => Yii::t('user', 'Created At'),
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
     * @return boolean Whether token has expired.
     */
    public function getIsExpired()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
            case self::TYPE_CONFIRM_NEW_EMAIL:
            case self::TYPE_CONFIRM_OLD_EMAIL:
                $expirationTime = $this->getSetting('confirmWithin');
                break;
            case self::TYPE_CONFIRM_NEW_MOBILE:
            case self::TYPE_CONFIRM_OLD_MOBILE:
                $expirationTime = $this->getSetting('confirmWithin');
                break;
            case self::TYPE_RECOVERY:
                $expirationTime = $this->getSetting('recoverWithin');
                break;
            default:
                throw new \RuntimeException();
        }
        return ($this->created_at + $expirationTime) < time();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $route = '/user/registration/confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = '/user/recovery/reset';
                break;
            case self::TYPE_CONFIRM_NEW_EMAIL:
            case self::TYPE_CONFIRM_OLD_EMAIL:
                $route = '/user/setting/confirm';
                break;
            case self::TYPE_CONFIRM_NEW_MOBILE:
            case self::TYPE_CONFIRM_OLD_MOBILE:
                $route = '/user/setting/mobile';
                break;
            default:
                throw new \RuntimeException();
        }

        return Url::to([$route, 'id' => $this->user_id, 'code' => $this->code], true);
    }

    /**
     * @inheritdoc
     * @return UserTokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserTokenQuery(get_called_class());
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
            static::deleteAll(['user_id' => $this->user_id, 'type' => $this->type]);
            $this->setAttribute('created_at', time());
            if ($this->type == self::TYPE_CONFIRM_NEW_MOBILE || $this->type == self::TYPE_CONFIRM_OLD_MOBILE) {
                $this->setAttribute('code', Yii::$app->security->generateRandomString(6));
            } else {
                $this->setAttribute('code', Yii::$app->security->generateRandomString());
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
//    public function afterSave($insert, $changedAttributes)
//    {
//        parent::afterSave($insert, $changedAttributes);
//
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
