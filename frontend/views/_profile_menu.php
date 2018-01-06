<?php
use yuncms\core\widgets\ListGroup;

/** @var yuncms\user\models\User $user */
$user = Yii::$app->user->identity;
$networksVisible = count(Yii::$app->authClientCollection->clients) > 0;

$items = [
    [
        'label' => Yii::t('user', 'Profile Setting'),
        'url' => ['/user/settings/profile'],
        'icon' => 'glyphicon glyphicon-user',
    ],
    [
        'label' => Yii::t('user', 'Security Setting'),
        'url' => ['/user/settings/account'],
        'icon' => 'glyphicon glyphicon-cog'
    ],
    [
        'label' => Yii::t('user', 'Avatar Setting'),
        'url' => ['/user/settings/avatar'],
        'icon' => 'glyphicon glyphicon-picture'
    ],
    [
        'label' => Yii::t('user', 'Authentication'),
        'url' => ['/authentication/authentication/index'],
        'icon' => 'glyphicon glyphicon-education',
        'visible' => Yii::$app->hasModule('authentication')
    ],
    [
        'label' => Yii::t('user', 'My Funds'),
        'url' => ['/wallet/wallet/index'],
        'icon' => 'fa fa-money',
        'visible' => Yii::$app->hasModule('wallet')
    ],
    [
        'label' => Yii::t('user', 'My Coin'),
        'url' => ['/coin/coin/index'],
        'icon' => 'fa fa-gift',
        'visible' => Yii::$app->hasModule('coin')
    ],
    [
        'label' => Yii::t('user', 'My Credit'),
        'url' => ['/credit/credit/index'],
        'icon' => 'fa fa-credit-card',
        'visible' => Yii::$app->hasModule('credit')
    ],
    [
        'label' => Yii::t('user', 'OAuth Apps'),
        'url' => ['/oauth2/client/index'],
        'icon' => 'glyphicon glyphicon-paperclip',
        'visible' => Yii::$app->hasModule('oauth2')
    ],
    [
        'label' => Yii::t('user', 'Social Networks'),
        'url' => ['/user/settings/networks'],
        'icon' => 'glyphicon glyphicon-retweet',
        'visible' => $networksVisible
    ],
];
?>

<?= ListGroup::widget([
    'options' => [
        'class' => 'list-group',
    ],
    'encodeLabels' => false,
    'itemOptions' => [
        'class' => 'list-group-item'
    ],
    'items' => $items
]) ?>
