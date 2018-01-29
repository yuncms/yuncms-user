<?php

namespace yuncms\user\models;

use yii\db\ActiveQuery;
use yii\authclient\ClientInterface as BaseClientInterface;

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

    /**
     * Finds an account by provider.
     * @param string $provider
     * @return UserSocialAccountQuery
     */
    public function byProvider($provider)
    {
        return $this->andWhere(['provider' => $provider]);
    }

    /**
     * Finds an account by code.
     * @param string $code
     * @return UserSocialAccountQuery
     */
    public function byCode($code)
    {
        return $this->andWhere(['code' => md5($code)]);
    }

    /**
     * Finds an account by id.
     * @param integer $id
     * @return UserSocialAccountQuery
     */
    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Finds an account by user_id.
     * @param integer $userId
     * @return UserSocialAccountQuery
     */
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Finds an account by client.
     * @param BaseClientInterface $client
     * @return UserSocialAccountQuery
     */
    public function byClient(BaseClientInterface $client)
    {
        return $this->andWhere(['provider' => $client->getId(), 'client_id' => $client->getUserAttributes()['id']]);
    }
}
