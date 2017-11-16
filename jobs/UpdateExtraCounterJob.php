<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\jobs;

use yii\base\BaseObject;
use yii\queue\RetryableJob;
use yuncms\user\models\User;
use yuncms\user\models\UserExtra;

/**
 * 更新扩展表计数器字段
 * @package yuncms\user\jobs
 */
class UpdateExtraCounterJob extends BaseObject implements RetryableJob
{
    /**
     * @var int user id
     */
    public $user_id;

    /**
     * @var string 字段名称
     */
    public $field;

    /**
     * @var integer 计数器
     */
    public $counter = 1;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if (($user = $this->getUser()) != null) {
            $user->extra->updateCounters([$this->field => $this->counter]);
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