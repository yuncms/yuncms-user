<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\notifications;

use Yii;
use yuncms\notification\Channel;
use yuncms\user\models\User;
use yuncms\notification\Notification;
use yuncms\notification\channels\EmailChannel;

/**
 * Class UserNotification
 * @package yuncms\user\notifications
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 1.0
 */
class UserNotification extends Notification
{

    const CATEGORY_USER_LOGIN = 'user_login';

    /**
     * @var User
     */
    public $user;

    /**
     * Get the notification's delivery channels.
     * @param Channel $channel
     * @return boolean
     */
    public function shouldSend($channel)
    {
        if ($channel->id == 'screen') {//WEB桌面通知
            if (!in_array($this->category, [self::CATEGORY_USER_LOGIN])) {//不是授权的分类，直接干掉
                return false;
            }

            if ($this->category == self::CATEGORY_USER_LOGIN) {//登录提醒不发送给WEB桌面
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        switch ($this->category) {
            case self::CATEGORY_USER_LOGIN:
                return Yii::t('user', 'Your account successfully landed.');
        }
    }

    /**
     * Override send to email channel
     *
     * @param $channel EmailChannel the email channel
     * @return void
     */
    public function toEmail($channel)
    {
        switch ($this->category) {
            case self::CATEGORY_USER_LOGIN:
                $subject = 'Your account successfully landed ' . Yii::$app->name;
                $template = 'user/landed';
                break;
        }

        $message = $channel->mailer->compose($template, [
            'user' => $this->user,
            'notification' => $this,
        ]);
        Yii::configure($message, $channel->message);

        $message->setTo($this->user->email);
        $message->setSubject($subject);
        $message->send($channel->mailer);
    }
}