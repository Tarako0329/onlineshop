<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AlterTblJuchuuMeisai extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `juchuu_meisai` CHANGE `bikou` `bikou` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}