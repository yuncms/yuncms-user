<?php

namespace yuncms\user\migrations;

use yii\db\Migration;

class M171113072326Create_user_extra_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        /**
         * 创建用户附表
         */
        $this->createTable('{{%user_extra}}', [
            'user_id' => $this->integer()->unsigned()->notNull()->comment('User ID'),
            'login_ip' => $this->string()->comment('Login Ip'),
            'login_at' => $this->integer()->unsigned()->comment('Login At'),
            'login_num' => $this->integer()->unsigned()->defaultValue(0)->comment('Login Num'),
            'views' => $this->integer()->unsigned()->defaultValue(0)->comment('Views'),
            'supports' => $this->integer()->unsigned()->defaultValue(0)->comment('Supports'),
            'followers' => $this->integer()->unsigned()->defaultValue(0)->comment('Followers'),
            'last_visit' => $this->integer()->unsigned()->comment('Last Visit'),
        ], $tableOptions);
        $this->addPrimaryKey('{{%user_extra}}', '{{%user_extra}}', 'user_id');
        $this->addForeignKey('{{%user_extra_fk_1}}', '{{%user_extra}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_extra}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171113072326Create_user_extra_table cannot be reverted.\n";

        return false;
    }
    */
}
