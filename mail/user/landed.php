<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yuncms\user\models\User $user
 * @var \yuncms\notification\Notification $notification
 */
?>

<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('user', 'Hello') ?>,
</p>

<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('user', 'Your account {username} on {datetime} at IP {ip} Login success!', [
        'username' => $user->username,
        'datetime' => Yii::$app->formatter->asDatetime($user->extra->login_at),
        'ip' => $user->extra->login_ip,
    ]) ?>,
</p>

<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('user', 'If this is not your own login, indicating that your account has been stolen! In order to reduce your loss, please {click} here to change your password immediately.', [
        'click' => Html::a('click', Url::to(['recovery/request'], true))
    ]) ?>,
</p>
