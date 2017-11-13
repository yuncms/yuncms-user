<?php

namespace yuncms\user\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[UserSocialAccount]].
 *
 * @see UserSocialAccount
 */
class UserSocialAccountQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /*public function active()
    {
        return $this->andWhere(['status' => UserSocialAccount::STATUS_PUBLISHED]);
    }*/

    /**
     * @inheritdoc
     * @return UserSocialAccount[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserSocialAccount|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
