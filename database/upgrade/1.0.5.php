<?php
class Journal_Upgrade_1_0_5 extends MIDASUpgrade
{
  public function preUpgrade()
    {

    }

  public function mysql()
    {
    $sql = "ALTER TABLE user ADD journalmodule_notification_comments tinyint(4) DEFAULT 1;";
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
