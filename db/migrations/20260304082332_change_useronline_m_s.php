<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class ChangeUseronlineMS extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `Users_online` CHANGE `yagou` `yagou` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '屋号', CHANGE `shacho` `shacho` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '代表者', CHANGE `jusho` `jusho` VARCHAR(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '屋号住所', CHANGE `mail` `mail` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL, CHANGE `mail_body` `mail_body` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '注文受付確認', CHANGE `mail_body_auto` `mail_body_auto` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '注文受付自動返信', CHANGE `mail_body_paid` `mail_body_paid` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '支払確認連絡', CHANGE `mail_body_sent` `mail_body_sent` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '発送連絡', CHANGE `mail_body_cancel` `mail_body_cancel` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'キャンセル受付メール', CHANGE `site_name` `site_name` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT 'サイト名（未使用）', CHANGE `logo` `logo` VARCHAR(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `site_pr` `site_pr` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'PR情報', CHANGE `chk_recept` `chk_recept` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '受注管理・受付（未使用）', CHANGE `chk_sent` `chk_sent` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '受注管理・発送（未使用）', CHANGE `chk_paid` `chk_paid` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '受注管理・入金（未使用）', CHANGE `cancel_rule` `cancel_rule` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'キャンセル規定', CHANGE `invoice` `invoice` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'インボイス未登録' COMMENT 'インボイス番号', CHANGE `stripe_id` `stripe_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'stripe connect id', CHANGE `upddatetime` `upddatetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}