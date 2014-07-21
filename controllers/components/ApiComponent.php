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
    $itemIds = array();
    
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

        $totalResults = $response->response->numFound;
        foreach($response->response->docs as $doc)
          {
          $itemIds[] = $doc->key;
          }
        }
      catch(Exception $e)
        {
        throw new Exception('Syntax error in query ', -1);
        }
      if($useCache) file_put_contents($cacheFile, JsonComponent::encode($itemIds));
      }

    sort($itemIds);
    $itemIds = array_reverse($itemIds);

    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $items = array();
    $count = 0;
    foreach($itemIds as $itemId)
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
        $level = $resourceDao->getCertificationLevel();
        $isCertified = 0;
        if(!empty($level) && is_numeric($level))
          {
          $isCertified = 1;
          }
        $statistics = "Download ".$item->getDownload()." ".(($item->getDownload() > 1)?"times":"time").", viewed ".$item->getView()." ".(($item->getView() > 1)?"times":"time");
        $items[] = array('total' => $totalResults, 'title' => htmlentities($item->getName(), ENT_COMPAT | ENT_HTML401, "UTF-8" ), 'rating' => (float)$rating['average'],
            'type' => $item->getType(), 'logo' => $resourceDao->getLogo(), 'id' => $item->getKey(), 'description' => htmlentities($item->getDescription(), ENT_COMPAT | ENT_HTML401, "UTF-8" ), 'authors' => $authors,
            'view' => $item->getView() ,'downloads' => $item->getDownload(), 'statistics' => $statistics,
            'revisionId' =>  $resourceDao->getRevision()->getKey(), "isCertified" => $isCertified, "certifiedLevel" => $level);
        $count++;
        if($count >= $limit)
          {
          break;
          }
        }
      }
    return $items;
    }
} // end class




