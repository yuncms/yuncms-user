<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\notifications;

use Yii;
use yuncms\notification\NotificationTrait;
use yuncms\notifications\contracts\NotificationInterface;
use yuncms\user\models\User;

/**
 * Class UserNotification
 * @package yuncms\user\notifications
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 1.0
 */
class UserNotification implements NotificationInterface
{

    use NotificationTrait;

    /**
     * @return object|\yuncms\notifications\messages\MailMessage
     * @throws \yii\base\InvalidConfigException
     */
    public function exportForMail()
    {
        return Yii::createObject([
            'class' => \yuncms\notifications\messages\MailMessage::class,
            'view' => ['html' => 'invoice-paid'],
            'viewData' => [
                'invoiceNumber' => 'test',
                'amount' => 'test'
            ]
        ]);
    }

    /**
     * @return object|\yuncms\notifications\messages\AliyunCloudPushMessage
     * @throws \yii\base\InvalidConfigException
     */
    public function exportForJPush()
    {
        return Yii::createObject([
            'class' => \yuncms\notifications\messages\JPushMessage::class,
            'title' => 'test',
            'body' => 'testst'
        ]);
    }

    /**
     * @return object|\yuncms\notifications\messages\AliyunCloudPushMessage
     * @throws \yii\base\InvalidConfigException
     */
    public function exportForAliyunCloudPush()
    {
        return Yii::createObject([
            'class' => \yuncms\notifications\messages\AliyunCloudPushMessage::class,
            'title' => 'test',
            'body' => 'testst'
        ]);
    }
}