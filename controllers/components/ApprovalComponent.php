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

class Journal_ApprovalComponent extends AppComponent
{

  /**
   * Get ArticlesWaitingForApproval
   * @param type $userDao
   * @return array of item
   */
  public function getArticlesWaitingForApproval($userDao)
    {
    // Check if editor 
    $isAdmin = $userDao->isAdmin();
    $listAdminFolders = array();
    if(!$isAdmin)
      {
      $groups = $userDao->getGroups();
      foreach($groups as $group)
        {
        if(strpos($group->getName(), "Issue_") !== false)
          {
          $listAdminFolders[] = (int) str_replace("Issue_", "", $group->getName());
          }
        if(strpos($group->getName(), "Administrator") !== false)
          {
          $folders = $group->getCommunity()->getFolder()->getFolders();
          foreach($folders as $f) $listAdminFolders[] = $f->getKey();
          }
        }
      }
    if(empty($listAdminFolders) && !$isAdmin) return array();
    
    $db = Zend_Registry::get('dbAdapter');
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", "approval_status");
    if(!$metadataDao) return array();
    $results = $db->query("SELECT itemrevision_id FROM metadatavalue WHERE metadata_id='".$metadataDao->getKey()."' AND value='1' ")
               ->fetchAll();
    
    $items = array();
    foreach($results as $result)
      {
      $revisionId = $result['itemrevision_id'];
      $item = MidasLoader::loadModel("ItemRevision")->load($revisionId)->getItem();
      $folder = end($item->getFolders());
      if($isAdmin || in_array($folder->getKey(), $listAdminFolders)) $items[] = $item;
      }
    return $items;
    }
} // end class
