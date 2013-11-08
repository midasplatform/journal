<?php
/*=========================================================================
 *
 *  Copyright OSHERA Consortium
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *         http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *=========================================================================*/

class Journal_SubmitController extends Journal_AppController
{
  function init()
    {

    }
    
  /** The first step of the submission process is  to select an issue */
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
    
  /** Form allowing the user to submit an article*/
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
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      $resourceDao->setRevision($revision);
      if(!MidasLoader::loadModel("Item")->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_WRITE) ||
              !$resourceDao->isAdmin($this->userSession->Dao))
        {
        throw new Zend_Exception("Permissions error.");
        }    
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
      $isNew = !$resourceDao->saved;
      // Create or update resource
      MidasLoader::loadModel("Item")->save($resourceDao);
      $itemRevisionDao = $resourceDao->getRevision();
      
      // create a new revision
      if(!$itemRevisionDao->saved)
        {        
        $itemRevisionDao->setChanges("");
        $itemRevisionDao->setUser_id($this->userSession->Dao->getKey());
        $itemRevisionDao->setDate(date('c'));
        $itemRevisionDao->setLicenseId(null);
        $lastExistingRevision = MidasLoader::loadModel("Item")->getLastRevision($resourceDao);
        MidasLoader::loadModel("Item")->addRevision($resourceDao, $itemRevisionDao);
        if($lastExistingRevision) // If new revision, copy previous bitstreams
          {
          $bitstreams = $lastExistingRevision->getBitstreams();
          foreach($bitstreams as $bitstream)
            {
            $bitstream->saved = false;
            $bitstream->bitstream_id = null;
            MidasLoader::loadModel("ItemRevision")->addBitstream($itemRevisionDao, $bitstream);
            }
          }
        }  
        
      MidasLoader::loadModel('Folder')->addItem($this->view->issueDao, $resourceDao);
      $resourceDao->enable();
      
      // Make sure the journal and issue editor and the author can manage the resource
      if($isNew)
        {
        $resourceDao->setSubmitter($this->userSession->Dao);
        $adminGroup = $resourceDao->getAdminGroup();
        MidasLoader::loadModel("Itempolicygroup")->createPolicy($adminGroup, $resourceDao, MIDAS_POLICY_ADMIN);
        MidasLoader::loadModel("Itempolicyuser")->createPolicy($this->userSession->Dao, $resourceDao, MIDAS_POLICY_WRITE);
        
        $policies = $this->view->issueDao->getFolderpolicygroup();
        foreach($policies as $policy)
          {
          if($policy->getPolicy() == MIDAS_POLICY_ADMIN)
            {
            MidasLoader::loadModel("Itempolicygroup")->createPolicy($policy->getGroup(), $resourceDao, MIDAS_POLICY_ADMIN);
            }
          }
        
        if($resourceDao->isAdmin($this->userSession->Dao))
          {
          MidasLoader::loadModel("Itempolicygroup")->createPolicy($anonymousGroup, $resourceDao, MIDAS_POLICY_READ);
          }
        }            
      
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
    
  /** Upload management. See jquery uploader library */
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
    new UploadHandler(array('upload_dir' => $this->getTempDirectory()."/uploaded/",  'user_dirs' => true,'orient_image' => false));
    }
    
  /** delete a resource */
  function deleteAction()
    {
    $this->requireAdminPrivileges();
    $item_id = $this->_getParam('itemId');  
    $revision_id = $this->_getParam('revisionId');  
    
    if(isset($item_id))
      {
      $item = MidasLoader::loadModel("Item")->load($item_id);      
      MidasLoader::loadModel("Item")->delete($item);
      $this->_redirect("/");
      }
    elseif(isset($revision_id))
      {
      $revision = MidasLoader::loadModel("ItemRevision")->load($revision_id);      
      $item = $revision->getItem();
      MidasLoader::loadModel("ItemRevision")->delete($revision);
      $revisionOld = MidasLoader::loadModel("Item")->getLastRevision($item);
      $this->_redirect("/journal/view/".$revisionOld->getKey());
      }
    }
    
  /** Page where the user select what to upload */
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
    
    // Check if public or private (If private, it means it requires approval
    $private = true;
    foreach($resourceDao->getItempolicygroup() as $policy)
      {
      if($policy->getGroupId() == MIDAS_GROUP_ANONYMOUS_KEY)
        {
        $private = false;
        break;
        }
      }
    
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
        if($private && $resourceDao->isAdmin($this->userSession->Dao)) // Approve
          {
          $anonymousGroup = MidasLoader::loadModel("Group")->load(MIDAS_GROUP_ANONYMOUS_KEY);
          MidasLoader::loadModel("Itempolicygroup")->createPolicy($anonymousGroup, $resourceDao, MIDAS_POLICY_READ);
          }
        elseif($private) // Send for approval
          {
          MidasLoader::loadComponent("Notification", "journal")->sendForApproval($resourceDao);
          }
        $this->_redirect("/journal/view/".$resourceDao->getRevision()->getKey());
        return;
        }
      }
      
    // sent to theview
    $this->view->isPrivate = $private;
    $this->view->isAdmin = $resourceDao->isAdmin($this->userSession->Dao);
    $this->view->resource = $resourceDao;
    $this->view->bitstreams = $bitstreams;
    $this->view->json['resource'] = $resourceDao->toArray();
    $this->view->json['revision'] = $revision_id;
    }
}//end class