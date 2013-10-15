<?php
class Reviewosehra_AppController extends MIDAS_GlobalModule
  {
  public $moduleName='reviewosehra';
  
  /**
   * Called before every pages 
   */
  public function preDispatch()
    {
    parent::preDispatch();
    $this->_helper->layout->setLayoutPath(BASE_PATH."/privateModules/journal/layouts");
    $this->_helper->layout->setLayout(MidasLoader::loadComponent("Layout", 'journal')->getLayoutName());
    }  
  } //end class
?>