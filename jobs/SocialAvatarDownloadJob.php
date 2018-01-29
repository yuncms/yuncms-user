<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\jobs;

use yii\base\BaseObject;
use yii\imagine\Image;
use yii\queue\RetryableJobInterface;
use yuncms\user\models\User;
use yuncms\user\UserTrait;

/**
 * Class SocialAvatarDownloadJob
 * @package yuncms\user\jobs
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 1.0
 */
class SocialAvatarDownloadJob extends BaseObject implements RetryableJobInterface
{
    use UserTrait;

    /**
     * @var int user id
     */
    public $user_id;

    /**
     * @var string 微信头像地址
     */
    public $faceUrl;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if (($user = $this->getUser()) != null) {
            $avatarPath = $this->getAvatarPath($user->id);
            $originalImage = $avatarPath . '_avatar.jpg';
            //下载图片
            if (($image = @file_get_contents($this->faceUrl)) != false) {
                //保存原图
                file_put_contents($originalImage, $image);
                //缩放
                Image::thumbnail($originalImage, 200, 200)->save($avatarPath . '_avatar_big.jpg', ['quality' => 100]);
                Image::thumbnail($avatarPath . '_avatar_big.jpg', 128, 128)->save($avatarPath . '_avatar_middle.jpg', ['quality' => 100]);
                Image::thumbnail($avatarPath . '_avatar_big.jpg', 48, 48)->save($avatarPath . '_avatar_small.jpg', ['quality' => 100]);
                $user->avatar = true;
                $user->save();
            };
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