<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AlterQALog extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `online_q_and_a` CHANGE `sts` `sender` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'customer or shop';");
        $this->execute("ALTER TABLE `online_q_and_a` CHANGE `name` `name` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE `online_q_and_a` CHANGE `sender` `sts` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'customer or shop';");
    }
}