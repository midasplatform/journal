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

class Journal_IndexController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
    
  /** Index (first page) action
   * This is the main page of the website whenre the articles are listed
   * It is also where the search is done
   */
  function indexAction()
    {   
    // fetch all the keywords and send them to the view
    $selectedCat = array();
    if(isset($_GET['category'])) $selectedCat = array($_GET['category']);
    $this->view->tree = MidasLoader::loadComponent("Tree", "journal")->getAllTrees(false, $selectedCat);
    if(isset($_GET['q'])) $this->view->query = $_GET['q'];
    if(isset($_GET['community']))$community = MidasLoader::loadModel ('Community')->load($_GET['community']);
    else if(isset($_GET['issue']))
      {
      $selectedIssueDao = MidasLoader::loadModel ('Folder')->load($_GET['issue']);
      $community = MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($selectedIssueDao));
      }
    else
      {
      $modulesConfig = Zend_Registry::get('configsModules');  
      $community = MidasLoader::loadModel("Community")->load($modulesConfig['journal']->defaultcommunity);
      }
    
    if(isset($community) && $community)
      {
      $communityIssues = MidasLoader::loadModel ('Folder')->getChildrenFoldersFiltered($community->getFolder(), null, MIDAS_POLICY_READ,
                                                         "date_update", "asc", 100000, 0);
      $activeIssues = MidasLoader::loadModel("Issue", 'journal')->findActiveIssues();
      $activeIssuesArray = array();
      $this->view->issues = array();
      foreach($activeIssues as $issue) $activeIssuesArray[] = $issue->getKey();
      foreach($communityIssues as $key => $issue)
        {
        if($issue->getName() == "Public" || $issue->getName() == "Private") continue;
        $issueDao = MidasLoader::loadModel("Folder")->initDao("Issue", $issue->toArray(), "journal");
        if(in_array($issue->getKey(), $activeIssuesArray)) $issueDao->active = true;
        else $issueDao->active = false;
        
        $this->view->issues[] = $issueDao;
        }

      }
    // send the tree to the JS files
    $this->view->json['trees'] = $this->view->tree;
    $this->view->json['selectedIssue'] = (isset($selectedIssueDao))?$selectedIssueDao->getKey():false;
    $this->view->json['selectedCommunity'] = (isset($community))?$community->getKey():false;
    $this->view->json['treeJournals'] = $this->view->treeJournals;
    $this->view->json['searchDisableCompletion'] = true;
    }
}//end class