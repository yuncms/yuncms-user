<?php

namespace yuncms\user\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_extra}}".
 *
 * @property integer $user_id
 * @property string $login_ip
 * @property integer $login_at
 * @property integer $login_num
 * @property integer $views
 * @property integer $supports
 * @property integer $followers
 * @property integer $last_visit
 *
 * @property User $user
 * @property UserProfile $profile
 *
 */
class UserExtra extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_extra}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'login_at', 'login_num', 'views', 'supports', 'followers', 'last_visit'], 'integer'],
            [['login_ip'], 'string', 'max' => 255],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('user', 'User ID'),
            'login_ip' => Yii::t('user', 'Login Ip'),
            'login_at' => Yii::t('user', 'Login At'),
            'login_num' => Yii::t('user', 'Login Num'),
            'views' => Yii::t('user', 'Views'),
            'supports' => Yii::t('user', 'Supports'),
            'followers' => Yii::t('user', 'Followers'),
            'last_visit' => Yii::t('user', 'Last Visit'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return UserExtraQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserExtraQuery(get_called_class());
    }

    /**
     * 获取指定字段排行榜
     * @param string $field 字段
     * @param int $limit
     * @return UserExtra[]
     */
    public static function top($field, $limit)
    {
        return static::find()->with('user')->with('profile')->orderBy([$field => SORT_DESC, 'last_visit' => SORT_DESC])->limit($limit)->all();
    }
}
