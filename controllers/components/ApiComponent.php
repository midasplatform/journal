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

require_once BASE_PATH.'/core/controllers/components/UtilityComponent.php';

/** Component for api methods */
class Journal_ApiComponent extends AppComponent
{
 /**
   * Create a big thumbnail for the given bitstream with the given width. It is used as the main image of the given item and shown in the item view page.
   * @param token (Optional) Authentication token
   * @param query The Lucene search query
   * @param limit (Optional) The limit of the search; defaults to 25
   * @return The list of items matching the search query
   */
  public function search($args)
    {
    $defaultCommunity = MidasLoader::loadModel("Setting")->getValueByName('defaultJournal', "journal");
    
    $componentLoader = new MIDAS_ComponentLoader();
    $solrComponent = $componentLoader->loadComponent('Solr', 'solr');
    if(file_exists(BASE_PATH."/modules/api/controllers/components/AuthenticationComponent.php"))
      {
      $authComponent = $componentLoader->loadComponent('Authentication', 'api');
      }           
    else  $authComponent = $componentLoader->loadComponent('Authentication');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    $useCache  = !$userDao && "text-journal.enable:true  AND ( text-journal.community:".$defaultCommunity." )" == $args['query'];  
    $cacheFile = UtilityComponent::getTempDirectory()."/homeSearch.json";
    $limit = array_key_exists('limit', $args) ? (int)$args['limit'] : 25;
    $offset = array_key_exists('offset', $args) ? (int)$args['offset'] : 0;
    $targetLevel = array_key_exists('level', $args) ? $args['level'] : 0;
    $itemIds = array();
    $totalResults = 0;
    if($useCache && file_exists($cacheFile) &&  (filemtime($cacheFile) > (time() - 60 * 60 * 24 * 1 ))) // 1 day cache
      {
      $itemIds = JsonComponent::decode(file_get_contents($cacheFile));
      }
    else
      {
      try
        {
        $index = $solrComponent->getSolrIndex();
        UtilityComponent::beginIgnoreWarnings(); //underlying library can generate warnings, we need to eat them
        
        $factor = 10;
        if($useCache) $factor = 100000; // Get all the ids when creating the cache
        $response = $index->search($args['query'], 0, $limit * $factor + $offset, array('fl' => '*,score')); //extend limit to allow some room for policy filtering
        UtilityComponent::endIgnoreWarnings();
        foreach($response->response->docs as $doc)
          {
          $itemIds[] = $doc->key;
          }
        if(!empty($targetLevel))
          {
          // Increase the factor to capture all available submissions for searching the target level
          $factor = 100000;
          $response = $index->search($args['secondQuery'], 0, $limit * $factor + $offset, array('fl' => '*,score')); //extend limit to allow some room for policy filtering
          }
        foreach($response->response->docs as $doc)
          {
          $itemIds[] = $doc->key;
          }
        }
      catch(Exception $e)
        {
        throw new Exception('Syntax error in query ', -1);
        }

      if($useCache)
        {
        file_put_contents($cacheFile, JsonComponent::encode($itemIds));
        }
      }

    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');

    $revisionIds = array();
    foreach($itemIds as $itemId)
      {
      $item = $itemModel->load($itemId);
      if($item && $itemModel->policyCheck($item, $userDao))
        {
        $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
        $revisionId = $resourceDao->getRevisionId();
        $revisionIds[$revisionId] = $itemId;
        }
      }

    // Sort in descending order by revisionId so
    // that newest revisions are displayed first.
    ksort($revisionIds);
    $revisionIds = array_reverse($revisionIds);

    $items = array();
    $count = 0;
    foreach($revisionIds as $revisionId => $itemId)
      {
      if($offset != 0)
        {
        $offset--;
        continue;
        }
      $item = $itemModel->load($itemId);
      if($item && $itemModel->policyCheck($item, $userDao))
        {
        $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
        $rating = MidasLoader::loadModel("Itemrating", 'ratings')->getAggregateInfo($item);
        $authors = join(", ", $resourceDao->getAuthorsFullNames());

        if($args['level'] !== '')
          {
          list($level,$foundRevisionID,$foundRevisionKey) = $resourceDao->getAllCertificationLevel($args['level']);
          }
        else
          {
          $level = $resourceDao->getCertificationLevel();
          }
        $isCertified = 0;
        if(!empty($level) && is_numeric($level))
          {
          $isCertified = 1;
          }

        $statistics = "Download ".$item->getDownload()." ".(($item->getDownload() > 1)?"times":"time").", viewed ".$item->getView()." ".(($item->getView() > 1)?"times":"time");
        if($targetLevel == 0 || (strpos($targetLevel,$level) !== false))
          {
          $totalResults++;
          $items[] = array('total' => $totalResults, 'title' => htmlentities($item->getName(), ENT_COMPAT | ENT_HTML401, "UTF-8" ),
            'rating' => (float)$rating['average'], 'type' => $item->getType(), 'logo' => $resourceDao->getLogo(),
            'id' => $item->getKey(), 'description' => htmlentities($item->getDescription(), ENT_COMPAT | ENT_HTML401, "UTF-8" ),
            'authors' => $authors, 'view' => $item->getView() ,'downloads' => $resourceDao->getDownload(), 'statistics' => $statistics,
            'revisionId' => $resourceDao->getRevision()->getKey(), "isCertified" => $isCertified, 'pastCertificationRevisionNum' => $foundRevisionID, "certifiedLevel" => $level,
            'pastCertificationRevisionKey' => $foundRevisionKey);

          $count++;
          }
        if($count >= $limit)
          {
          break;
          }
        }
      }
    return $items;
    }
    

  public function usersearch($args)
    {
    // This is necessary in order to avoid session lock and being able to run two
    // ajax requests simultaneously
    session_write_close();
    
    // Search for the users
    $UsersDao = MidasLoader::loadModel('User')->getUsersFromSearch($args['term'], $this->userSession->Dao, 14, false);
   
    // Compute how many of each we should display
    $nusers = count($UsersDao);

    // Return the JSON results
    $results = array();
    $id = 1;
    $n = 0;
    
    // User
    $n = 0;
    foreach($UsersDao as $userDao)
      {
      if($n == 10)
        {
        break;
        }
      $label = $userDao->getFirstname().' '.$userDao->getLastname();
      $value = $label;
      $result = array('id' => $id,
                      'label' => $label." (".$userDao->getEmail().")",
                      'value' => $value,
                      'category' => 'Users');

      $result['userid'] = $userDao->getKey();
      $id++;
      $n++;
      $results[] = $result;
      }
    // Other live search options
    foreach($OtherOptions as $option)
      {
      $results[] = $option;
      }

    return $results;
    }
} // end class





