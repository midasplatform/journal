<?php
class Journal_Upgrade_1_0_3 extends MIDASUpgrade
{
  public function preUpgrade()
    {

    }

  public function mysql()
    {
    $sql = "CREATE TABLE IF NOT EXISTS `journal_disclaimer` (
        `disclaimer_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `name` text NOT NULL,
        `description` text NOT NULL,
        PRIMARY KEY (`disclaimer_id`)
      );";
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
