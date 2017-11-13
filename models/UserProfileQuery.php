<?php

namespace yuncms\user\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[UserProfile]].
 *
 * @see UserProfile
 */
class UserProfileQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /*public function active()
    {
        return $this->andWhere(['status' => UserProfile::STATUS_PUBLISHED]);
    }*/

    /**
     * @inheritdoc
     * @return UserProfile[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserProfile|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * 只看妹子
     * @return $this
     */
    public function female()
    {
        return $this->andWhere(['gender' => UserProfile::GENDER_FEMALE]);
    }
}
