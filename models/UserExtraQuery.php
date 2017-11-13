<?php

namespace yuncms\user\models;

/**
 * This is the ActiveQuery class for [[UserExtra]].
 *
 * @see UserExtra
 */
class UserExtraQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /*public function active()
    {
        return $this->andWhere(['status' => UserExtra::STATUS_PUBLISHED]);
    }*/

    /**
     * @inheritdoc
     * @return UserExtra[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserExtra|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
