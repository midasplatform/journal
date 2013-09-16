<?php
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
    $componentLoader = new MIDAS_ComponentLoader();
    $solrComponent = $componentLoader->loadComponent('Solr', 'solr');
    if(file_exists(BASE_PATH."/modules/api/controllers/components/AuthenticationComponent.php"))
      {
      $authComponent = $componentLoader->loadComponent('Authentication', 'api');
      }           
    else  $authComponent = $componentLoader->loadComponent('Authentication');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);

    $limit = array_key_exists('limit', $args) ? (int)$args['limit'] : 25;
    $itemIds = array();
    try
      {
      $index = $solrComponent->getSolrIndex();

      UtilityComponent::beginIgnoreWarnings(); //underlying library can generate warnings, we need to eat them
      $response = $index->search($args['query'], 0, $limit * 5, array('fl' => '*,score')); //extend limit to allow some room for policy filtering
      UtilityComponent::endIgnoreWarnings();

      $totalResults = $response->response->numFound;
      foreach($response->response->docs as $doc)
        {
        $itemIds[] = $doc->key;
        }
      }
    catch(Exception $e)
      {
      throw new Exception('Syntax error in query', -1);
      }

    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $items = array();
    $count = 0;
    foreach($itemIds as $itemId)
      {
      $item = $itemModel->load($itemId);
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      if($item && $itemModel->policyCheck($item, $userDao))
        {
        $rating = MidasLoader::loadModel("Itemrating", 'ratings')->getAggregateInfo($item);
        $authors = join(", ", $resourceDao->getAuthorsFullNames());
        $statistics = "Download ".$item->getDownload()." ".(($item->getDownload() > 1)?"times":"time").", viewed ".$item->getView()." ".(($item->getView() > 1)?"times":"time");
        $items[] = array('total' => $totalResults, 'title' => $item->getName(), 'rating' => (float)$rating['average'],
            'type' => $item->getType(), 'id' => $item->getKey(), 'description' => $item->getDescription(), 'authors' => $authors,
            'view' => $item->getView() ,'downloads' => $item->getDownload(), 'statistics' => $statistics,
            'revisionId' =>  $resourceDao->getRevision()->getKey());
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




