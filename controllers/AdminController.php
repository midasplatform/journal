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

class Journal_AdminController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
    
  // List publications waiting for approval
  function approvalAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception("Please log in.", 404);
      }
    if(empty($this->view->waitingApproval))
      {
      throw new Zend_Exception("No approval required", 404);
      }
     
    $articles = array();
    foreach($this->view->waitingApproval as $item)
      {
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      $articles[] = array('total' => count($this->view->waitingApproval), 'title' => $item->getName(), 
            'type' => $item->getType(), 'logo' => $resourceDao->getLogo(), 'id' => $item->getKey(), 'description' => $item->getDescription(), 'authors' => $authors,
            'view' => $item->getView() ,'downloads' => $item->getDownload(),
            'revisionId' =>  $resourceDao->getRevision()->getKey());
      }
     
    $this->view->json["articles"] = $articles;
    }
    
  /** Manage journals and issues*/
  function issuesAction()
    {   
    if(!$this->logged)
      {
      throw new Zend_Exception("Please log in.", 404);
      }
    $this->view->communities = MidasLoader::loadModel('Community')->getAll();
    $this->view->isAdmin = $this->userSession->Dao->isAdmin();
    }
    
  /** Manage journals and issues editors and members*/
  function groupusersAction()
    {   
    $groupId = $this->_getParam('groupId');  
    $showUsers = $this->_getParam("showmembers");
    $group = MidasLoader::loadModel('Group')->load($groupId);
    if($group === false)
      {
      throw new Zend_Exception("This group doesn't exist.", 404);
      }      

    $community = $group->getCommunity();
    if(!$this->logged ||  (!$this->userSession->Dao->isAdmin() 
            && !MidasLoader::loadModel('Group')->userInGroup($this->userSession->Dao, $community->getAdminGroup())))
      {
      throw new Zend_Exception("Permission error.", 404);
      }
      
    if($this->_request->isPost())      
      {
      $this->disableLayout();
      $this->disableView();

      $remove = $this->_getParam('remove');
      $add = $this->_getParam('add');
      $groupId = $this->_getParam('groupId');
      $userId = $this->_getParam('userId');
      $group = MidasLoader::loadModel('Group')->load($groupId);
      $user = MidasLoader::loadModel('User')->load($userId);
      if(!$user || !$group)
        {
        throw new Zend_Exception('Invalid user or group parameter');
        }

      if(isset($remove))
        {
        MidasLoader::loadModel('Group')->removeUser($group, $user);
        echo JsonComponent::encode(array(true, 'Removed user '.$user->getFullName().' from group '.$group->getName()));
        }
      
      if(isset($add))
        {
        MidasLoader::loadModel('Group')->addUser($group, $user);
        if($group->getKey() == $community->getAdminGroup()->getKey())
          {
          MidasLoader::loadModel('Group')->addUser($community->getMemberGroup(), $user);
          }
        echo JsonComponent::encode(array(true, $this->t('Changes saved')));
        }
      }

    $this->view->showMember = isset($showUsers) && $showUsers == 1 && $group->getKey() == $community->getAdminGroup()->getKey();
    $this->view->editorgroup = $group;
    $this->view->membergroup = $community->getMemberGroup();
    $this->view->name = $this->_getParam('name');
    $this->view->json['editorgroup'] = $group->toArray();
    $this->view->json['membergroup'] = $this->view->membergroup ->toArray();
    $this->view->json['community'] = $community->toArray();
    }
    
  /** Edit help/faq content */
  function helpAction()
    {
    $this->requireAdminPrivileges();
    $settingModel = MidasLoader::loadModel('Setting');
    try
      {
      $this->view->helpcontent = $settingModel->getValueByName('help_text', 'journal');
      $this->view->faqcontent = $settingModel->getValueByName('faq_text', 'journal');
      $this->view->aboutcontent = $settingModel->getValueByName('about_text', 'journal');
      }
    catch(Exception $e)
      {
      $this->view->helpcontent = "";
      $this->view->faqcontent = "";
      $this->view->aboutcontent = "";
      }

    if($this->_request->isPost())
      {
      $settingModel->setConfig('help_text', $_POST['helpcontent'], 'journal');
      $settingModel->setConfig('faq_text', $_POST['faqcontent'], 'journal');
      $settingModel->setConfig('about_text', $_POST['aboutcontent'], 'journal');
      $this->_redirect("/journal/help");
      }      
    }
    
  /** Edit disclaimers */
  function disclaimerAction()
    {
    $this->requireAdminPrivileges();
    $this->view->disclaimers = MidasLoader::loadModel("Disclaimer", "journal")->getAll();
    }
    
  /** Edit disclaimers */
  function editdisclaimerAction()
    {
    $this->requireAdminPrivileges();
    $disclaimerId = $this->_getParam("disclaimerId");
    
    $disclaimer = MidasLoader::loadModel("Disclaimer", "journal")->load($disclaimerId);
    if(!$disclaimer || !$disclaimer->saved) 
      {
      $disclaimer = MidasLoader::newDao("DisclaimerDao", "journal");
      $disclaimer->setName("");
      $disclaimer->setDescription("");
      }
            
    if($this->_request->isPost())
      {
      if(isset($_POST['delete']) && !empty($_POST['delete']) && is_numeric($disclaimerId))
        {
        MidasLoader::loadModel("Disclaimer", "journal")->delete($disclaimer);
        }
      else
        {
        $disclaimer->setName($_POST['name']);
        $disclaimer->setDescription($_POST['description']);
        MidasLoader::loadModel("Disclaimer", "journal")->save($disclaimer);
        }
      $this->_redirect("/journal/admin/disclaimer");
      }    
    
    $this->view->disclaimer = $disclaimer;
    }
    
  /** Edit an issue */
  function editissueAction()
    {  
    $this->view->disablePolicySelector = false;
    // load resource if it exists
    $folderId = $this->_getParam('folderId');  
    $communityId = $this->_getParam('communityId');  
    if(isset($folderId))
      {
      $folder = MidasLoader::loadModel("Folder")->load($folderId);
      $issueDao = MidasLoader::loadModel("Folder")->initDao("Issue", $folder->toArray(), "journal");
      $community = MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($folder));
      }   
    else
      {
      $issueDao = MidasLoader::newDao('IssueDao', 'journal');
      $community = MidasLoader::loadModel("Community")->load($communityId);
      if($community->getPrivacy() == 1)
        {
        $this->view->disablePolicySelector = true;
        $issueDao->setDefaultPolicy(1);
        }
      }
      
    
    if(!$community || !$this->logged 
            || !$this->userSession->Dao->isAdmin()
            || !MidasLoader::loadModel('Group')->userInGroup($this->userSession->Dao, $community->getAdminGroup()))
      {
      throw new Zend_Exception("Permission error.", 404);
      }
      
    if($this->_request->isPost())
      {
      $deleteIssue = $this->_getParam("deleteIssue");
      if(isset($deleteIssue))
        {
        $issueDao->delete();
        $this->_redirect("/journal/admin/issues");
        }
      if(isset($communityId) && !$issueDao->saved)
        {
        $community  = MidasLoader::loadModel("Community")->load($communityId);
        $folder = $community->getFolder();
        $issueDao->setParentId($folder->getKey());
        $issueDao->setName($_POST['name']);
        MidasLoader::loadModel("Folder")->save($issueDao);
        $issueDao->InitValues();
        $anonymousGroup = MidasLoader::loadModel("Group")->load(MIDAS_GROUP_ANONYMOUS_KEY);
        MidasLoader::loadModel("Folderpolicygroup")->createPolicy($anonymousGroup, $issueDao, MIDAS_POLICY_READ);        
        $editorGroup = MidasLoader::loadModel("Group")->createGroup($community, "Issue_".$issueDao->getKey());
        MidasLoader::loadModel("Folderpolicygroup")->createPolicy($editorGroup, $issueDao, MIDAS_POLICY_ADMIN);
        }
        
      if(!isset($_POST['defaultpolicy']) || $_POST['defaultpolicy'] != 1)
        {
        $_POST['defaultpolicy'] = 0;
        }
        
      foreach($_POST as $key => $value)
        {
        if(isset($issueDao->$key)) 
          {
          $issueDao->$key = $value;
          }
        }
      $issueDao->initialized = true;
      $issueDao->save();
      $this->_redirect("/journal/admin/issues");
      }
    
    $this->view->isNew = !isset($folderId);
    $this->view->issue = $issueDao;
    }
    
    
  /** Manage the categories*/
  function categoriesAction()
    {
    $this->requireAdminPrivileges();
    
    $cacheFile = UtilityComponent::getTempDirectory()."/treeCache.json";
    if(file_exists($cacheFile))
      {
      unlink($cacheFile);
      }
      
    // if add a new tree
    if($this->_request->isPost() && !empty($_POST['newtree']))
      {
      //save the new tree
      $categoryDao = MidasLoader::newDao('CategoryDao', 'journal');
      $categoryDao->setName($_POST['newtree']);
      $categoryDao->setParentId(-1);
      MidasLoader::loadModel("Category", "journal")->save($categoryDao);
      }
      
    // if add a new category
    if($this->_request->isPost() && !empty($_POST['newCategory']))
      {
      //save the new tree
      $categoryDao = MidasLoader::newDao('CategoryDao', 'journal');
      $categoryDao->setName($_POST['newCategory']);
      
      $parentDao = MidasLoader::loadModel("Category", "journal")->load($_POST['parentCategory']);
      if($parentDao)
        {
        $categoryDao->setParentId($parentDao->getKey());
        MidasLoader::loadModel("Category", "journal")->save($categoryDao);
        }
      }
    
    if($this->_request->isPost() && isset($_POST['deleteChild']) && is_numeric($_POST['deleteChild']))
      {
      $categoryDao = MidasLoader::loadModel("Category", "journal")->load($_POST['deleteChild']);
      MidasLoader::loadModel("Category", "journal")->delete($categoryDao);
      }
    
    // fetch all the keywords and send them to the view
    $this->view->tree = MidasLoader::loadComponent("Tree", "journal")->getAllTrees();
    // send the tree to the JS files
    $this->view->json['trees'] = $this->view->tree;
    }
  
}//end class