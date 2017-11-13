<?php

namespace yuncms\user\jobs;

use yii\base\BaseObject;
use yii\queue\RetryableJob;
use yuncms\user\models\User;

/**
 * 记录最后活动时间
 * @package yuncms\user\jobs
 */
class LastVisitJob extends BaseObject implements RetryableJob
{
    /**
     * @var int user id
     */
    public $user_id;

    /**
     * @var int 最后活动时间
     */
    public $time;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if (($user = $this->getUser()) != null) {
            $user->extra->updateAttributes(['last_visit' => $this->time]);
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
