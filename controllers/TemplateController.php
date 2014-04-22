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

class Handle_TemplateController extends Handle_AppController
{
// Initialization method. Called before every Action
  function init()
    {
    $handle = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    if(empty($handle) || !is_numeric($handle))
      {
      parent::init();
      }
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", "handle");
    if(!$metadataDao)  $metadataDao = MidasLoader::loadModel('Metadata')->addMetadata(MIDAS_METADATA_TEXT, "journal", "handle", "");
    $db = Zend_Registry::get('dbAdapter');
    $sql = $db->select()
            ->from('metadatavalue')
            ->where('metadata_id  = '.$metadataDao->getKey())
            ->where('value  = '.$handle)
            ->order('value DESC')->limit(1);

    $row = $db->fetchRow($sql);

    if(isset($row['itemrevision_id']))
      {
      $revision = MidasLoader::loadModel("ItemRevision")->load($row['itemrevision_id']);
      if($revision)
        {
        $this->_redirect("/journal/view/".$revision->getKey());
        }
      }
    parent::init();    
    }
}//end class