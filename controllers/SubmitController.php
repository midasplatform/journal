<?php

class Reviewosehra_SubmitController extends Reviewosehra_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
   
  function indexAction()
    {  
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return;      
      }
    $revision_id = $this->_getParam('revision_id');
    if(!isset($revision_id) || !is_numeric($revision_id))
      {
      throw new Zend_Exception("revision_id should be a number");
      }
    $revision = MidasLoader::loadModel("ItemRevision")->load($revision_id);
    $itemDao = $revision->getItem();
    if($itemDao === false || !MidasLoader::loadModel("Item")->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_READ))
      {
      throw new Zend_Exception("This item doesn't exist or you don't have the permissions.");
      }
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
    $resourceDao->setRevision($revision);

    // look for existing review
    $reviews = MidasLoader::loadModel("Review", 'reviewosehra')->getByRevision($revision);
    $review = false; //TODO
    
    $questionslists = MidasLoader::loadModel("Questionlist", 'reviewosehra')->getAll();
    if($review)
      {
      //TODO
      }
    else
      {
      $categories = $resourceDao->getCategories();
      $mainListTmp = end($questionslists);
      foreach($categories as $cat)
        {
        foreach($questionslists as $list)
          {
          if($cat == $list->getCategoryId())
            {
            $mainListTmp = $list;
            }
          }
        }
      $mainList = $mainListTmp->toArray();
      }
    $this->view->resource = $resourceDao;
    $this->view->listArray = $mainList;
    $this->view->json['listArray'] = $mainList;
    $this->view->lists = $questionslists;
    }
}//end class