<?php

class Reviewosehra_IndexController extends Reviewosehra_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
   
    
  /** Index (first page) action*/
  function indexAction($arg)
    {   
    $revisionId = $this->_getParam("revisionId");
    $isAdmin = $this->_getParam("isAdmin");
  
    $revision = MidasLoader::loadModel("ItemRevision")->load($revisionId);
    $reviews = MidasLoader::loadModel("Review", "reviewosehra")->getByRevision($revision);
    
    $this->view->reviews = array(OSERHAREVIEW_LIST_PEERREVIEW =>  array('complete' => array(), "notcomplete"=> array())
        , OSERHAREVIEW_LIST_FINALREVIEW => array('complete' => array(), "notcomplete"=> array()));
    
    if(empty($reviews)) $this->view->reviews = array();
    
    foreach($reviews as $review)
      {
      $this->view->reviews[$review->getType()][$review->getComplete() == 100?'complete':'notcomplete'][] = $review;
      }
      
    $this->view->isAdmin = $isAdmin;
    }
    
  /** Index (first page) action*/
  function adminmenuAction()
    {   
    }
}//end class