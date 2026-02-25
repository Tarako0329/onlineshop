<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddStatusToUsers extends AbstractMigration
{
    public function up(): void
    {
        // ここにSQLを書く（例：$this->execute("CREATE TABLE ...");）
        $this->execute("ALTER TABLE `Users` ADD `login_type` VARCHAR(255) NOT NULL DEFAULT '-' AFTER `answer`");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}