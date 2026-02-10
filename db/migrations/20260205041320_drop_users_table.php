<?php
use Phinx\Migration\AbstractMigration;

class DropUsersTable extends AbstractMigration
{
    /**
     * 反映時の処理 (Up)
     */
    public function up(): void
    {
      $sql = "DROP TABLE users_test;";
        
       $this->execute($sql);
    }

    /**
     * 戻す時の処理 (Down)
     */
    public function down(): void
    {
        $sql = "DROP TABLE users_test;";
        $this->execute($sql);
    }
}
?>

