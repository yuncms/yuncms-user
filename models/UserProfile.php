<?php

namespace yuncms\user\models;

use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_profile}}".
 *
 * @property integer $user_id
 * @property integer $gender
 * @property string $mobile
 * @property string $email
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $location
 * @property string $address
 * @property string $website
 * @property string $timezone
 * @property string $birthday
 * @property integer $current
 * @property string $qq
 * @property string $weibo
 * @property string $wechat
 * @property string $facebook
 * @property string $twitter
 * @property string $company
 * @property string $company_job
 * @property string $school
 * @property string $introduction
 * @property string $bio
 *
 * @property User $user
 * @property UserExtra $extra
 *
 * @property-read string $genderName 性别
 * @property-read string $currentName 工作状态
 */
class UserProfile extends ActiveRecord
{
    // 性别
    const GENDER_UNCONFIRMED = 0b0;
    const GENDER_MALE = 0b1;
    const GENDER_FEMALE = 0b10;

    //当前状态
    const CURRENT_OTHER = 0b0;//其他
    const CURRENT_WORK = 0b1;//正常工作
    const CURRENT_FREELANCE = 0b10;//自由职业者
    const CURRENT_START = 0b11;//创业
    const CURRENT_OUTSOURCE = 0b100;//外包
    const CURRENT_JOB = 0b101;//求职
    const CURRENT_STUDENT = 0b110;//学生

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //性别
            ['gender', 'default', 'value' => self::GENDER_UNCONFIRMED],
            ['gender', 'in', 'range' => [
                self::GENDER_MALE,
                self::GENDER_FEMALE,
                self::GENDER_UNCONFIRMED
            ]],

            //职业状态
            ['current', 'default', 'value' => self::CURRENT_OTHER],
            ['current', 'in', 'range' => [
                self::CURRENT_OTHER,
                self::CURRENT_WORK,
                self::CURRENT_FREELANCE,
                self::CURRENT_START,
                self::CURRENT_OUTSOURCE,
                self::CURRENT_JOB,
                self::CURRENT_STUDENT,
            ]],

            //手机号
            ['mobile', 'match', 'pattern' => User::$mobileRegexp],
            ['mobile', 'string', 'min' => 11, 'max' => 11],
            [
                'mobile',
                'yuncms\core\validators\MobileValidator',
                'when' => function ($model) {
                    return $model->country == 'China';
                }
            ],

            ['email', 'email'],
            ['email', 'trim'],

            ['birthday', 'date', 'format' => 'php:Y-m-d', 'min' => '1900-01-01', 'max' => date('Y-m-d')],
            ['birthday', 'string', 'max' => 15],

            ['website', 'url'],

            ['qq', 'integer', 'min' => 10001, 'max' => 9999999999],
            ['timezone', 'validateTimeZone'],

            [['bio'], 'string'],
            [['country', 'province', 'city', 'location', 'address', 'company', 'company_job', 'school', 'introduction'], 'string', 'max' => 255],


            [['weibo', 'wechat', 'facebook', 'twitter'], 'string', 'max' => 50],
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
            'gender' => Yii::t('user', 'Gender'),
            'mobile' => Yii::t('user', 'Mobile'),
            'email' => Yii::t('user', 'Email'),
            'country' => Yii::t('user', 'Country'),
            'province' => Yii::t('user', 'Province'),
            'city' => Yii::t('user', 'City'),
            'location' => Yii::t('user', 'Location'),
            'address' => Yii::t('user', 'Address'),
            'website' => Yii::t('user', 'Website'),
            'timezone' => Yii::t('user', 'Timezone'),
            'birthday' => Yii::t('user', 'Birthday'),
            'current' => Yii::t('user', 'Current'),
            'qq' => Yii::t('user', 'QQ'),
            'weibo' => Yii::t('user', 'Weibo'),
            'wechat' => Yii::t('user', 'Wechat'),
            'facebook' => Yii::t('user', 'Facebook'),
            'twitter' => Yii::t('user', 'Twitter'),
            'company' => Yii::t('user', 'Company'),
            'company_job' => Yii::t('user', 'Company Job'),
            'school' => Yii::t('user', 'School'),
            'introduction' => Yii::t('user', 'Introduction'),
            'bio' => Yii::t('user', 'Bio'),
        ];
    }

    /**
     * 获取性别的字符串标识
     */
    public function getGenderName()
    {
        switch ($this->gender) {
            case self::GENDER_UNCONFIRMED:
                $genderName = Yii::t('user', 'Secrecy');
                break;
            case self::GENDER_MALE:
                $genderName = Yii::t('user', 'Male');
                break;
            case self::GENDER_FEMALE:
                $genderName = Yii::t('user', 'Female');
                break;
            default:
                throw new \RuntimeException('Your database is not supported!');
        }
        return $genderName;
    }

    /**
     * 获取职业的字符串标识
     * @return string
     */
    public function getCurrentName()
    {
        switch ($this->current) {
            case self::CURRENT_OTHER:
                $currentName = Yii::t('user', 'Other');
                break;
            case self::CURRENT_WORK:
                $currentName = Yii::t('user', 'Work');
                break;
            case self::CURRENT_FREELANCE:
                $currentName = Yii::t('user', 'Freelance');
                break;
            case self::CURRENT_START:
                $currentName = Yii::t('user', 'Start');
                break;
            case self::CURRENT_OUTSOURCE:
                $currentName = Yii::t('user', 'Outsource');
                break;
            case self::CURRENT_JOB:
                $currentName = Yii::t('user', 'Job');
                break;
            case self::CURRENT_STUDENT:
                $currentName = Yii::t('user', 'Student');
                break;
            default:
                throw new \RuntimeException('Your database is not supported!');
        }
        return $currentName;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getExtra()
    {
        return $this->hasOne(UserExtra::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return UserProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserProfileQuery(get_called_class());
    }

    /**
     * 验证时区
     * Adds an error when the specified time zone doesn't exist.
     * @param string $attribute the attribute being validated
     * @param array $params values for the placeholders in the error message
     */
    public function validateTimeZone($attribute, $params)
    {
        if (!in_array($this->$attribute, timezone_identifiers_list())) {
            $this->addError($attribute, Yii::t('user', 'Time zone is not valid'));
        }
    }

    /**
     * Get the user's time zone.
     * Defaults to the application timezone if not specified by the user.
     * @return \DateTimeZone
     */
    public function getTimeZone()
    {
        try {
            return new \DateTimeZone($this->timezone);
        } catch (\Exception $e) {
            // Default to application time zone if the user hasn't set their time zone
            return new \DateTimeZone(Yii::$app->timeZone);
        }
    }

    /**
     * Set the user's time zone.
     * @param DateTimeZone $timeZone
     * @internal param DateTimeZone $timezone the timezone to save to the user's profile
     */
    public function setTimeZone(DateTimeZone $timeZone)
    {
        $this->setAttribute('timezone', $timeZone->getName());
    }

    /**
     * Converts DateTime to user's local time
     * @param DateTime $dateTime the datetime to convert
     * @return DateTime
     */
    public function toLocalTime(DateTime $dateTime = null)
    {
        if ($dateTime === null) {
            $dateTime = new DateTime();
        }

        return $dateTime->setTimezone($this->getTimeZone());
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
