<?php

namespace yuncms\user\migrations;

use yii\db\Query;
use yii\db\Migration;

class M171113072516Add_backend_menu extends Migration
{

    public function safeUp()
    {
        $this->batchInsert('{{%admin_menu}}', ['name', 'parent', 'route', 'icon', 'sort', 'data'], [
            ['用户管理', 5, '/user/user/index', 'fa-user', 2, NULL],
        ]);
        $id = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '用户管理', 'parent' => 5])->scalar($this->getDb());

        $this->batchInsert('{{%admin_menu}}', ['name', 'parent', 'route', 'visible', 'sort'], [
            ['用户设置', $id, '/user/user/settings', 0, NULL],
            ['新建用户', $id, '/user/user/create', 0, NULL],
            ['用户查看', $id, '/user/user/view', 0, NULL],
            ['用户修改', $id, '/user/user/update-profile', 0, NULL],
            ['账户详情', $id, '/user/user/update', 0, NULL],
        ]);
    }

    public function safeDown()
    {
        $parentId = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '用户管理', 'parent' => 5])->scalar($this->getDb());
        $id = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '用户设置', 'parent' => $parentId])->scalar($this->getDb());
        $this->delete('{{%admin_menu}}', ['id' => $id, 'parent' => $parentId]);
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171113072516Add_backend_menu cannot be reverted.\n";

        return false;
    }
    */
}
