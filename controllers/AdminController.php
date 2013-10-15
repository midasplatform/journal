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
    
  /** Manage journals and issues*/
  function issuesAction()
    {   
    $this->requireAdminPrivileges();
    $this->view->communities = MidasLoader::loadModel('Community')->getAll();
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
    
  /** Edit an issue */
  function editissueAction()
    {   
    $this->requireAdminPrivileges();
    
    // load resource if it exists
    $folderId = $this->_getParam('folderId');  
    $communityId = $this->_getParam('communityId');  
    if(isset($folderId))
      {
      $folder = MidasLoader::loadModel("Folder")->load($folderId);
      $issueDao = MidasLoader::loadModel("Folder")->initDao("Issue", $folder->toArray(), "journal");
      }   
    else
      {
      $issueDao = MidasLoader::newDao('IssueDao', 'journal');
      }
      
    if($this->_request->isPost())
      {
      if(isset($communityId) && !$issueDao->saved)
        {
        $community  = MidasLoader::loadModel("Community")->load($communityId);
        $folder = $community->getFolder();
        $issueDao->setParentId($folder->getKey());
        $issueDao->setName($_POST['name']);
        MidasLoader::loadModel("Folder")->save($issueDao);
        $issueDao->InitValues();
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
    $this->view->issue = $issueDao;
    }
    
    
  /** Manage the categories*/
  function categoriesAction()
    {
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
    
  /** Migrate */
  function migratemidas2Action()
    {
    $this->requireAdminPrivileges();
    $this->disableLayout();
    $this->disableView();
    MidasLoader::loadComponent("Migrate", "journal")->migrate($_GET);
    }
}//end class