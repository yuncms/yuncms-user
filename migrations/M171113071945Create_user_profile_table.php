<?php

namespace yuncms\user\migrations;

use yii\db\Migration;

class M171113071945Create_user_profile_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        /**
         * 创建用户资料表
         */
        $this->createTable('{{%user_profile}}', [
            'user_id' => $this->integer()->unsigned()->notNull()->comment('User ID'),
            'gender' => $this->smallInteger(1)->notNull()->defaultValue(0)->comment('Gender'),
            'mobile' => $this->string()->comment('Mobile'),
            'email' => $this->string()->comment('Email'),
            'country' => $this->string()->comment('Country'),
            'province' => $this->string()->comment('Province'),
            'city' => $this->string()->comment('City'),
            'location' => $this->string()->comment('Location'),
            'address' => $this->string()->comment('Address'),
            'website' => $this->string()->comment('Website'),
            'timezone' => $this->string(100)->comment('Timezone'),//默认格林威治时间
            'birthday' => $this->string(15)->comment('Birthday'),
            'current' => $this->smallInteger(1)->comment('Current'),
            'qq' => $this->string(11)->comment('QQ'),
            'weibo' => $this->string(50)->comment('Weibo'),
            'wechat' => $this->string(50)->comment('Wechat'),
            'facebook' => $this->string(50)->comment('Facebook'),
            'twitter' => $this->string(50)->comment('Twitter'),
            'company' => $this->string()->comment('Company'),
            'company_job' => $this->string()->comment('Company Job'),
            'school' => $this->string()->comment('School'),
            'introduction' => $this->string()->comment('Introduction'),
            'bio' => $this->text()->comment('Bio'),
        ], $tableOptions);
        $this->addPrimaryKey('{{%user_profile}}', '{{%user_profile}}', 'user_id');
        $this->addForeignKey('{{%user_profile_fk_1}}', '{{%user_profile}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_profile}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171113071945Create_user_profile_table cannot be reverted.\n";

        return false;
    }
    */
}
