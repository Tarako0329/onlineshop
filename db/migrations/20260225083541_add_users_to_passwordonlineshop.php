<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddUsersToPasswordonlineshop extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `Users` ADD `password_onlineshop` VARCHAR(255) NOT NULL DEFAULT '-' AFTER `login_type`;");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}