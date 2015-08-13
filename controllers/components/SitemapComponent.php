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

/** Layout  component */
class Journal_SitemapComponent extends AppComponent
{  
  public function generate()
    {    
    $componentLoader = new MIDAS_ComponentLoader();
    $solrComponent = $componentLoader->loadComponent('Solr', 'solr');
    $index = $solrComponent->getSolrIndex();
    UtilityComponent::beginIgnoreWarnings(); //underlying library can generate warnings, we need to eat them

    $response = $index->search("text-journal.enable:true", 0, 99999, array('fl' => '*,score')); //extend limit to allow some room for policy filtering
    UtilityComponent::endIgnoreWarnings();
    
    $fc = Zend_Controller_Front::getInstance();
    $root = UtilityComponent::getServerURL().$fc->getBaseUrl();
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
    $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    
    // Home
    $xml .= '<url>'."\n";
    $xml .= "<loc>".$root."/journal</loc>\n";
    $xml .= "<changefreq>weekly</changefreq>\n";
    $xml .= "<priority>1.0</priority>\n";
    $xml .= "<image:image>\n";
    $xml .= "  <image:loc>http://osehra.org/profiles/drupal_commons/themes/commons_osehra_earth/images/logo.png</image:loc> \n";
    $xml .= "</image:image>\n";    
    $xml .= '</url>'."\n";
    
    // Help
    $xml .= '<url>'."\n";
    $xml .= "<loc>".$root."/help</loc>\n";
    $xml .= "<changefreq>monthly</changefreq>\n";
    $xml .= "<priority>0.6</priority>\n";
    $xml .= '</url>'."\n";
    
    // About
    $xml .= '<url>'."\n";
    $xml .= "<loc>".$root."/help/about</loc>\n";
    $xml .= "<changefreq>monthly</changefreq>\n";
    $xml .= "<priority>0.6</priority>\n";
    $xml .= '</url>'."\n";
    
    foreach($response->response->docs as $doc)
      {      
      $item = MidasLoader::loadModel("Item")->load($doc->key);
      if(!$item) continue;
      if(MidasLoader::loadModel("Item")->policyCheck($item, null))
        {
        $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
        $revision = $resourceDao->getRevision();
        $xml .= '<url>'."\n";
        $xml .= "<loc>".$root."/journal/view/".$revision->getKey()."</loc>\n";
        $xml .= "<changefreq>monthly</changefreq>\n";
        $xml .= "<priority>0.3</priority>\n";
        if($resourceDao->getLogo())
          {
          $xml .= "<image:image>\n";
          $xml .= "  <image:loc>".$root."/journal/view/logo/?revisionId=".$revision->getKey()."</image:loc> \n";
          $xml .= "</image:image>\n";    
          }        
        $xml .= '</url>'."\n";
        }      
      }    
    $xml .= '</urlset>'."\n";
    
    file_put_contents(BASE_PATH."/sitemap.xml", $xml);
    }
  
} // end class

/*
 * <?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" 
  xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
  <url> 
    <loc>http://www.example.com/foo.html</loc> 
    <image:image>
       <image:loc>http://example.com/image.jpg</image:loc> 
    </image:image>
    <video:video>     
      <video:content_loc>
        http://www.example.com/video123.flv
      </video:content_loc>
      <video:player_loc allow_embed="yes" autoplay="ap=1">
        http://www.example.com/videoplayer.swf?video=123
      </video:player_loc>
      <video:thumbnail_loc>
        http://www.example.com/thumbs/123.jpg
      </video:thumbnail_loc>
      <video:title>Grilling steaks for summer</video:title>  
      <video:description>
        Get perfectly done steaks every time
      </video:description>
    </video:video>
  </url>
</urlset>
 */