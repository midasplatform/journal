<?php
class Journal_Upgrade_1_0_2 extends MIDASUpgrade
{
  public function preUpgrade()
    {

    }

  public function mysql()
    {
    $sql = "ALTER TABLE bitstream ADD journalmodule_type tinyint(4);";
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
