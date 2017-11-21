<?php

namespace yuncms\user\jobs;

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use yuncms\user\models\User;

/**
 * Class ResetLoginDataJob.
 */
class ResetLoginDataJob extends BaseObject implements RetryableJobInterface
{
    public $user_id;

    public $ip;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if (($user = $this->getUser()) != null) {
            $user->extra->updateAttributes(['login_at' => time(), 'login_ip' => $this->ip, 'login_num' => $user->extra->login_num + 1]);
        }
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return User::findOne(['id' => $this->user_id]);
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}
