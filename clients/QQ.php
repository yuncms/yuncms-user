<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\user\clients;

use Yii;
use xutl\authclient\QQ as BaseQQ;

/**
 * Class Qq
 * @package yuncms\user\clients
 */
class QQ extends BaseQQ implements ClientInterface
{
    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return Yii::t('user', 'QQ');
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return isset($this->getUserAttributes()['username']) ? $this->getUserAttributes()['username'] : null;
    }
}