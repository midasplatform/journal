<?php

class Reviewosehra_IndexController extends Reviewosehra_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
   
    
  /** Index (first page) action*/
  function indexAction()
    {   
    $revisionId = $this->_getParam("revisionId");
    $isAdmin = $this->_getParam("isAdmin");
  
    $revision = MidasLoader::loadModel("ItemRevision")->load($revisionId);
    $reviews = MidasLoader::loadModel("Review", "reviewosehra")->getByRevision($revision);
    
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $revision->getItem()->toArray(), "journal");
    $resourceDao->setRevision($revision);
    
    $this->view->reviewPhase = $resourceDao->getMetaDataByQualifier("reviewPhase");
    if($this->view->reviewPhase) $this->view->reviewPhase = $this->view->reviewPhase->getValue();
    else $this->view->reviewPhase = OSERHAREVIEW_LIST_PEERREVIEW;
    
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