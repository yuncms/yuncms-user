<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user;

use Yii;
use yii\web\User;
use yii\web\GroupUrlRule;
use yii\i18n\PhpMessageSource;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;
use yuncms\user\jobs\LastVisitJob;
use yuncms\user\jobs\ResetLoginDataJob;
use yuncms\user\frontend\Module as FrontendModule;
use yuncms\user\wechat\Module as WeChatModule;

/**
 * Class Bootstrap
 * @package yuncms/user
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * 初始化
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApplication) {
            $app->controllerMap['user'] = [
                'class' => 'yuncms\user\console\UserController',
            ];
        } else if ($app->hasModule('user') && ($module = $app->getModule('user')) instanceof Module) {
            //监听用户活动时间
            /** @var \yii\web\UserEvent $event */
            $app->on(WebApplication::EVENT_AFTER_REQUEST, function ($event) use ($app) {
                if (!$app->user->isGuest && Yii::$app->has('queue')) {
                    //记录最后活动时间
                    Yii::$app->queue->push(new LastVisitJob(['user_id' => $app->user->identity->id, 'time' => time()]));
                }
            });

            if ($module instanceof WeChatModule || $module instanceof FrontendModule) {
                $configUrlRule = ['prefix' => $module->urlPrefix, 'rules' => $module->urlRules,];
                if ($module->urlPrefix != 'user') {
                    $configUrlRule['routePrefix'] = 'user';
                }
                $app->urlManager->addRules([new GroupUrlRule($configUrlRule)], false);
                //监听用户登录事件
                /** @var \yii\web\UserEvent $event */
                $app->user->on(User::EVENT_AFTER_LOGIN, function ($event) use ($app) {
                    //记录最后登录时间记录最后登录IP记录登录次数
                    Yii::$app->queue->push(new ResetLoginDataJob(['user_id' => $app->user->identity->getId(), 'ip' => Yii::$app->request->userIP]));
                });
                Yii::$container->set('yii\web\User', [
                    'enableAutoLogin' => true,
                    'loginUrl' => ['/user/security/login'],
                    'identityClass' => 'yuncms\user\models\User',
                ]);
                //设置用户所在时区
//                $app->on(\yii\web\Application::EVENT_BEFORE_REQUEST, function ($event) use ($app) {
//                    if (!$app->user->isGuest && $app->user->identity->profile->timezone) {
//                        $app->setTimeZone($app->user->identity->profile->timezone);
//                    }
//                });
            }

        }
        /**
         * 注册语言包
         */
        if (!isset($app->get('i18n')->translations['user*'])) {
            $app->get('i18n')->translations['user*'] = [
                'class' => PhpMessageSource::className(),
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
}