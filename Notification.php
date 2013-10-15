<?php
class Reviewosehra_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'reviewosehra';

  /** init notification process*/
  public function init()
    {
    $this->addCallBack('CALLBACK_JOURNAL_REVIEW', 'getReviewAction');
    $this->addCallBack('CALLBACK_JOURNAL_ADMIN_MENU', 'getAdminMenuAction');
    }//end init
 
  public function getReviewAction($params)
    {
    $return = array();
    $return['action'] = "index";
    $return['controller'] = "index";
    $return['module'] = "reviewosehra";
    $return['params'] = array('reviewId' => $params['resource']->getRevision()->getKey());
    return $return;
    }
    
  public function getAdminMenuAction($params)
    {
    $return = array();
    $return['action'] = "adminmenu";
    $return['controller'] = "index";
    $return['module'] = "reviewosehra";
    $return['params'] = array();
    return $return;
    }
  } //end class
?>

