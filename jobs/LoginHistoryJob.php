<?php

namespace yuncms\user\jobs;

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use yuncms\user\models\User;
use yuncms\user\models\UserLoginHistory;

/**
 * Class LoginHistoryJob
 * @package yuncms\user\jobs
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 1.0
 */
class LoginHistoryJob extends BaseObject implements RetryableJobInterface
{
    public $user_id;

    public $ip;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if (($user = $this->getUser()) != null) {
            $model = new UserLoginHistory(['ip' => $this->ip]);
            $model->save();
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
