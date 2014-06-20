<?php
class Journal_Upgrade_1_0_4 extends MIDASUpgrade
{
  public function preUpgrade()
    {

    }

  public function mysql()
    {
    $sql = "ALTER TABLE user ADD journalmodule_notification_submission tinyint(4) DEFAULT 1;";
    $this->db->query($sql);   
    $sql = "ALTER TABLE user ADD journalmodule_notification_review tinyint(4) DEFAULT 1;";
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
