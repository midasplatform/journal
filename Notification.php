<?php
/** notification manager*/

require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';
class Journal_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'journal';
  public $_moduleComponents=array('Api');

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    }//end init
 
  } //end class
?>

