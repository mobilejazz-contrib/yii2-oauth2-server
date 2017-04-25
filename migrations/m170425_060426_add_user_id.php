<?php

use yii\db\Schema;

class m170425_060426_add_user_id extends \yii\db\Migration
{

    public function up()
    {
        $this->dropPrimaryKey('username', '{{%oauth_users}}');
        $this->addColumn('{{%oauth_users}}', 'user_id', $this->primaryKey(11));

        return true;
    }

    public function down()
    {

        $this->dropColumn('{{%oauth_users}}', 'user_id', $this->integer(11)->notNull());
        $this->addPrimaryKey('username', '{{%oauth_users}}', 'username');

        return true;
    }
}
