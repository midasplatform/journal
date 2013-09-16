<?php
class Journal_Upgrade_1_0_1 extends MIDASUpgrade
{
  public function preUpgrade()
    {

    }

  public function mysql()
    {
    $sql = "CREATE TABLE IF NOT EXISTS `journal_category` (
        `category_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `parent_id` bigint(20) NOT NULL,
        `name` varchar(255) NOT NULL,
        PRIMARY KEY (`category_id`)
      );
      ";
    $this->db->query($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `journal_folder` (
        `not_used_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `folder_id` bigint(20) NOT NULL ,
        `paperdue_date` timestamp ,
        `decision_date` timestamp ,
        `publication_date` timestamp ,
        `logo` varchar(255) NOT NULL DEFAULT '',
        `defaultpolicy` tinyint(4) NOT NULL DEFAULT 0,
        `short_description` text NOT NULL DEFAULT '',
        `related_link` text NOT NULL DEFAULT '',
        `introductory_text` text NOT NULL DEFAULT '',
        `authorLicense` text NOT NULL DEFAULT '',
        `readerLicense` text NOT NULL DEFAULT '',
        PRIMARY KEY (`not_used_id`)
      );
      ";
    $this->db->query($sql);
    }

  public function pgsql()
    {

    }

  public function postUpgrade()
    {
    }
}
?>
