<?php

class Journal_SubmitController extends Journal_AppController
{
  function init()
    {

    }
    
  /** select an issue */
  function selectissueAction()
    {
    if(!$this->logged)$this->haveToBeLogged();
    $this->view->issues = MidasLoader::loadModel("Issue", 'journal')->findActiveIssues();
    foreach($this->view->issues as $key => $issue)
      {
      $community = MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($issue));
      if(isset($_GET['community']) && $community->getKey() !=  $_GET['community']) unset($this->view->issues[$key]);
      }
    }
    
  /** index action*/
  function indexAction()
    {    
    if(!$this->logged)$this->haveToBeLogged();
    // load resource if it exists
    $item_id = $this->_getParam('itemId');  
    $revision_id = $this->_getParam('revisionId');  
    $issueId = $this->_getParam('issue');  
    // New Revision
    if(isset($item_id))
      {
      $item = MidasLoader::loadModel("Item")->load($item_id);      
      if(!MidasLoader::loadModel("Item")->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_WRITE))
        {
        throw new Zend_Exception("Permissions error.");
        }        
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      $resourceDao->setRevision("New");
      $folder = end($item->getFolders());
      $this->view->json['showlicence'] = 1;
      }   
    // Edit
    else if(isset($revision_id))
      {
      $revision = MidasLoader::loadModel("ItemRevision")->load($revision_id);    
      if(!$revision)
        {
        throw new Zend_Exception("Unable to find revision.");
        }        
      $item = $revision->getItem();  
      if(!MidasLoader::loadModel("Item")->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_WRITE))
        {
        throw new Zend_Exception("Permissions error.");
        }        
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      $resourceDao->setRevision($revision);
      $folder = end($item->getFolders());
      $this->view->json['showlicence'] = 0;
      }
    // New Resource
    else
      {
      $resourceDao = MidasLoader::newDao('ResourceDao', 'journal');
      $resourceDao->setRevision("New");
      $folder = MidasLoader::loadModel("Folder")->load($issueId);
      if(!$folder)throw new Zend_Exception("Unable to find issuse.");      
      $this->view->json['showlicence'] = 1;
      }
      
    $this->view->issueDao = MidasLoader::loadModel("Folder")->initDao("Issue", $folder->toArray(), "journal");
      
    // Process form result
    if($this->_request->isPost())
      {
      $resourceDao->setName($_POST['title']);
      $resourceDao->setDescription($_POST['description']);
      $resourceDao->setType($_POST['type']);     
      $anonymousGroup = MidasLoader::loadModel("Group")->load(MIDAS_GROUP_ANONYMOUS_KEY);
      // Create or update resource
      MidasLoader::loadModel("Item")->save($resourceDao);
            
      $itemRevisionDao = $resourceDao->getRevision();
      // create first revision
      if(!$itemRevisionDao->saved)
        {        
        $itemRevisionDao->setChanges("");
        $itemRevisionDao->setUser_id($this->userSession->Dao->getKey());
        $itemRevisionDao->setDate(date('c'));
        $itemRevisionDao->setLicenseId(null);
        MidasLoader::loadModel("Item")->addRevision($resourceDao, $itemRevisionDao);
        }            
      MidasLoader::loadModel("Itempolicygroup")->createPolicy($anonymousGroup, $resourceDao, MIDAS_POLICY_READ);
      MidasLoader::loadModel('Folder')->addItem($this->view->issueDao, $resourceDao);
      $resourceDao->enable();
      
      $resourceDao->setInstitution($_POST['institution']);     
      $resourceDao->setAuthors(array($_POST['firstname'], $_POST['lastname']));     
      $resourceDao->setCategories($_POST['category']);     
      $resourceDao->setTags($_POST['tag']);     
              
      // Update search index
      Zend_Registry::get('notifier')->callback('CALLBACK_CORE_ITEM_SAVED', array(
          'item' => $resourceDao,
          'metadataChanged' => true));
      
      $this->_redirect("/journal/submit/upload?revisionId=".$itemRevisionDao->getKey());
      return;
      }
      
    // send the variables to the view 
    $this->view->resource = $resourceDao;
    
    // fetch all the keywords and send them to the view
    $this->view->json['trees'] = MidasLoader::loadComponent("Tree", "journal")->getAllTrees(false, $resourceDao->getCategories());
    }    
    
  function uploadhandlerAction()
    {
    error_reporting(E_ALL | E_STRICT);
    $this->disableLayout();
    $this->disableView();
    require(dirname(__FILE__).'/components/UploadHandler.php');
    if(!file_exists($this->getTempDirectory()."/uploaded"))
      {
      mkdir($this->getTempDirectory()."/uploaded");
      }
    $upload_handler = new UploadHandler(array('upload_dir' => $this->getTempDirectory()."/uploaded/",  'user_dirs' => true,'orient_image' => false));
    }
    
  function uploadAction()
    {
    $revision_id = $this->_getParam('revisionId');  
    $processUpload = $this->_getParam('processUpload');  

    // load resource if it exists
    $revision = MidasLoader::loadModel("ItemRevision")->load($revision_id);    
    if(!$revision)
        {
        throw new Zend_Exception("Unable to find revision.");
        }        
    $item = $revision->getItem(); 
    if(!MidasLoader::loadModel("Item")->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_WRITE))
        {
        throw new Zend_Exception("Permissions error.");
        }       
    if(!MidasLoader::loadModel("Item")->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_WRITE))
      {
      throw new Zend_Exception("Permissions error.");
      }        
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
    $resourceDao->setRevision($revision);
    
    if(isset($processUpload))
      {
      require(dirname(__FILE__).'/components/UploadHandler.php');
      $upload_handler = new UploadHandler(array('upload_dir' => $this->getTempDirectory()."/uploaded/", 'user_dirs' => true, 'orient_image' => false),false);
      $files = $upload_handler->get(false);
      foreach($files['files'] as $file)
        {   
        $filepath = $this->getTempDirectory()."/uploaded/".  session_id()."/".$file->name;
        if(file_exists($filepath))
          {
          MidasLoader::loadComponent('Upload')->createNewRevision($this->userSession->Dao, $file->name, $filepath,
                                                      "", $item->getKey(), $revision->getRevision(), null,
                                                      '', false);
          }
        }
      }
      
    $bitstreams = $resourceDao->getRevision()->getBitstreams(); // the bitstreams are the files
    
    if($this->_request->isPost())
      {
      $this->disableLayout();
      $this->disableView();
      if(isset($_POST['deletebitstream']))
        {
        $bitstream = MidasLoader::loadModel("Bitstream")->load($_POST['deletebitstream']);
        if($bitstream && $bitstream->getItemrevision()->getItem()->getKey() == $resourceDao->getKey())
          {
          MidasLoader::loadModel("Bitstream")->delete($bitstream);
          }
        return;
        }
      if(isset($_POST['finish']))
        {
        // We make sure Midas didn't create a new revision. If it did, we delete them
        $revisions = $resourceDao->getRevisions();
        foreach($revisions as $revision)
          {
          if($revision->getRevision() != 1)
            {
            foreach($revision->getBitstreams() as $b)
              {
              $b->setItemrevisionId($resourceDao->getRevision()->getKey());
              MidasLoader::loadModel("Bitstream")->save($b);
              }
            MidasLoader::loadModel("ItemRevision")->delete($revision);
            }
          }
        $this->_redirect("/journal/view/".$resourceDao->getRevision()->getKey());
        return;
        }
      }
    
    // sent to theview
    $this->view->resource = $resourceDao;
    $this->view->bitstreams = $bitstreams;
    $this->view->json['resource'] = $resourceDao->toArray();
    $this->view->json['revision'] = $revision_id;
    }
}//end class