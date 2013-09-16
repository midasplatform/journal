<?php

class Journal_ViewController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    if(isset($actionName) && is_numeric($actionName))
      {
      $this->_forward('index', null, null, array('revisionId' => $actionName));
      }
    parent::init();    
    }    
        
  /** List all the journals */
  function journalsAction()
    {   
    $this->view->communities = MidasLoader::loadModel('Community')->getAll();
    }    
    
  /** Display a resource*/
  function indexAction()
    {
    $revisionId = $this->_getParam("revisionId");
    if(!isset($revisionId) || !is_numeric($revisionId))
      {
      throw new Zend_Exception("revisionId should be a number");
      }
    $revisionDao = MidasLoader::loadModel("ItemRevision")->load($revisionId);
    if($revisionDao === false)
      {
      throw new Zend_Exception("This item doesn't exist.", 404);
      }
    $itemDao = $revisionDao->getItem();    
    if(!MidasLoader::loadModel("Item")->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_READ))
      {
      throw new Zend_Exception('Read permission required', 403);
      }
      
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
    $resourceDao->setRevision($revisionDao);
    
    MidasLoader::loadModel("Item")->incrementViewCount($resourceDao);

    // Send resource to the view
    $this->view->resource = $resourceDao;
    $this->view->issue =  end($resourceDao->getFolders());
    $this->view->community =  MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot( $this->view->issue));
    $this->view->creationDate = MidasLoader::loadComponent("Date")->formatDate(strtotime($resourceDao->getDateCreation()));
    $this->view->termFrequency = file_get_contents("http://localhost:8983/solr/admin/luke?fl=text-journal.tags&wt=json&numTerms=200&reportDocCount=false");
    $this->view->isAdmin = MidasLoader::loadModel("Item")->policyCheck($resourceDao, $this->userSession->Dao, MIDAS_POLICY_ADMIN);

    // Send to javascript
    $this->view->json['item'] = $itemDao->toArray();
    $this->view->json['item']['tags'] = $resourceDao->getTags();
    $this->view->json['item']['isAdmin'] = $this->view->isAdmin;
    $this->view->json['item']['isModerator'] = $this->view->isModerator;
    $this->view->json['modules'] = Zend_Registry::get('notifier')->callback('CALLBACK_CORE_ITEM_VIEW_JSON', array('item' => $itemDao));
    }
}//end class