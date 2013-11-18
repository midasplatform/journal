<?php

/* =========================================================================
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
 * ========================================================================= */

class Journal_RssController extends Journal_AppController {

  /** Create RSS feed */
  function indexAction()
    {
    $this->disableLayout();
    $this->disableView();

    $cacheFile = UtilityComponent::getTempDirectory() . "/rss.xml";
    if(false && file_exists($cacheFile) && (filemtime($cacheFile) > (time() - 60 * 60 * 24 * 6 ))) // 1 day cache
      {
      echo file_get_contents($cacheFile);
      return;
      }

    $modulesConfig = Zend_Registry::get('configsModules');
    $adminEmail = $modulesConfig['journal']->adminemail;

    $feedArray = array(
        //required
        'title' => Zend_Registry::get('configGlobal')->application->name,
        'link' => UtilityComponent::getServerURL() . $this->view->webroot,
        // required
        'charset' => 'utf-8',
        // optional
        'description' => Zend_Registry::get('configGlobal')->application->description,
        'author' => 'Kitware',
        'email' => 'charles.marion@kitware.com',
        // optional, ignored if atom is used
        'webmaster' => $adminEmail,
        // optional
        'copyright' => 'Kitware',
        'image' => MidasLoader::loadComponent("Layout", 'journal')->getLogoUrl(),
        'language' => 'en',
        // optional, ignored if atom is used
        'ttl' => 60 * 12,
        'entries' => array());
    
    $resources = MidasLoader::loadComponent("Api", "journal")->search(array('limit' => 15, "query" => "text-journal.enable:true" ));
    foreach($resources as $resourceRaw)
      {
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", MidasLoader::loadModel("Item")->load($resourceRaw['id'])->toArray(), "journal");
      $issue = end($resourceDao->getFolders());
      $feedArray['entries'][] =
              array(
                  //required
                  'title' => $resourceRaw["title"],
                  'link' => UtilityComponent::getServerURL().$this->view->webroot."/journal/view/".$resourceRaw['id'],
                  // required, only text, no html
                  'description' => $resourceRaw['description'],
                  // optional
                  'guid' => $resourceRaw['id'],               
                  // optional
                  'lastUpdate' => strtotime($resourceDao->getRevision()->getDate()),                 
                  // optional, list of the attached categories
                  'category' => array(
                      array(
                          // required
                          'term' => 'Issue - '.$issue->getName(),
                      )
                  ),
      );
      }

    Zend_Feed::importArray($feedArray, 'rss')->send();
    }

}

//end class