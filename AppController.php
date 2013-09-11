<?php
class Googleoauth_AppController extends MIDAS_GlobalModule
  {
  public $moduleName='googleoauth';
  
  /**
   * Called before every pages 
   */
  public function preDispatch()
    {
    parent::preDispatch();
    }
  
  } //end class
?>