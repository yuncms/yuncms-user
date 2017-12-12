<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user;

use Yii;
use yii\helpers\FileHelper;

/**
 * Class UserTrait
 *
 * @property Module $module
 * @package yuncms\user
 */
trait UserTrait
{
    /**
     * 获取模块配置
     * @param string $key
     * @param null $default
     * @return bool|mixed|string
     */
    public function getSetting($key, $default = null)
    {
        $value = Yii::$app->settings->get($key, 'user', $default);
        if ($key == 'avatarUrl' || $key == 'avatarPath') {
            return Yii::getAlias($value);
        }
        return $value;
    }

    /**
     * 获取头像的存储路径
     * @param int $userId
     * @return string
     * @throws \yii\base\Exception
     */
    public function getAvatarPath($userId)
    {
        $avatarPath = $this->getSetting('avatarPath') . '/' . $this->getAvatarSubPath($userId);
        if (!is_dir($avatarPath)) {
            FileHelper::createDirectory($avatarPath);
        }
        return $avatarPath . substr($userId, -2);
    }

    /**
     * 获取指定用户头像访问Url
     * @param int $userId 用户ID
     * @return string
     */
    public function getAvatarUrl($userId)
    {
        return $this->getSetting('avatarUrl') . '/' . $this->getAvatarSubPath($userId) . substr($userId, -2);
    }

    /**
     * 计算用户头像子路径
     *
     * @param int $userId 用户ID
     * @return string
     */
    public function getAvatarSubPath($userId)
    {
        $id = sprintf("%09d", $userId);
        $dir1 = substr($id, 0, 3);
        $dir2 = substr($id, 3, 2);
        $dir3 = substr($id, 5, 2);
        return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
    }

    /**
     * @return null|\yii\base\Module
     */
    public function getModule()
    {
        return Yii::$app->getModule('user');
    }

    /**
     * 给用户发送邮件
     * @param string $to 收件箱
     * @param string $subject 标题
     * @param string $view 视图
     * @param array $params 参数
     * @return boolean
     */
    public function sendMessage($to, $subject, $view, $params = [])
    {
        if (empty($to)) {
            return false;
        }
        $message = Yii::$app->mailer->compose([
            'html' => '@yuncms/user/mail/' . $view,
            'text' => '@yuncms/user/mail/text/' . $view
        ], $params)->setTo($to)->setSubject($subject);
        return $message->send();
    }
}