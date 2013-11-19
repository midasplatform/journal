<?php
class Reviewosehra_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'reviewosehra';

  /** init notification process*/
  public function init()
    {
    $this->addCallBack('CALLBACK_JOURNAL_REVIEW', 'getReviewAction');
    $this->addCallBack('CALLBACK_JOURNAL_MANAGE', 'getManageMenu');
    $this->addCallBack('CALLBACK_JOURNAL_ADMIN_MENU', 'getAdminMenuAction');
    $this->addCallBack('CALLBACK_JOURNAL_GET_STATS', 'getStats');
    }//end init
 
  public function getManageMenu($params)
    {
    if($params['isAdmin'])
      {
      $fc = Zend_Controller_Front::getInstance();
      return "<li><a href='" .$fc->getBaseUrl() . 
              "/reviewosehra/admin/manage?itemId=" . 
              $params['resource']->getKey() . "'>Manage reviews</a></li>";
      }
    else
      {
      return "";
      }
    }
    
  public function getStats()
    {
    $reviews = count(MidasLoader::loadModel("Review", 'reviewosehra')->getAll());
    return array((($reviews >1)? $reviews." reviews":$reviews." review"));
    }
    
  public function getReviewAction($params)
    {
    $return = array();
    $return['action'] = "index";
    $return['controller'] = "index";
    $return['module'] = "reviewosehra";
    $return['params'] = array('revisionId' => $params['resource']->getRevision()->getKey(), "isAdmin" => $params['isAdmin']);
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

