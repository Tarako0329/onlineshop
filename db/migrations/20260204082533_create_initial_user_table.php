<?php
use Phinx\Migration\AbstractMigration;

class CreateInitialUserTable extends AbstractMigration
{
    /**
     * 反映時の処理 (Up)
     */
    public function up(): void
    {
        $sql = "
            CREATE TABLE users_test (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
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

