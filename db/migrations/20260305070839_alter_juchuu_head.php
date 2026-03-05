<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AlterJuchuuHead extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `juchuu_head` CHANGE `st_name` `st_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '送り先宛名', CHANGE `st_yubin` `st_yubin` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '送り先郵便番号', CHANGE `st_jusho` `st_jusho` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '送り先住所', CHANGE `st_tel` `st_tel` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '送り先TEL'");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}