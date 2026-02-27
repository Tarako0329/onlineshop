<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class DropUsersColm extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `Users` DROP `password_onlineshop`;");
        $this->execute("ALTER TABLE `Users` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'IPASS以外は識別子ID';");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}