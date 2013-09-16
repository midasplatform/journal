<?php
class Journal_AppController extends MIDAS_GlobalModule
  {
  public $moduleName='journal';
  
  /**
   * Called before every pages 
   */
  public function preDispatch()
    {
    parent::preDispatch();
    
    // Select the module's layout
    $this->_helper->layout->setLayoutPath(dirname(__FILE__)."/layouts");
    $this->_helper->layout->setLayout('journal');
    $this->view->json['dynamicHelp'] = array();
    }
  
  } //end class
?>