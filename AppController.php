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
class Journal_AppController extends MIDAS_GlobalModule
  {
  public $moduleName='journal';
  
  /**
   * Called before every pages 
   */
  public function preDispatch()
    {
    ob_start(); 
    parent::preDispatch();
    ob_clean(); 
    
    $fc = Zend_Controller_Front::getInstance();
    $controller = $fc->getRequest()->getControllerName();
    
    // Select the module's layout
    if($controller != "config")
      {
      $this->_helper->layout->setLayoutPath(dirname(__FILE__)."/layouts");
      $this->_helper->layout->setLayout(MidasLoader::loadComponent("Layout", 'journal')->getLayoutName());
      }
    $this->view->json['dynamicHelp'] = array();
    
    // Create footer stats
    $cacheFile = UtilityComponent::getTempDirectory()."/mainStats.json";
    if(file_exists($cacheFile) &&  (filemtime($cacheFile) > (time() - 60 * 60 * 24 * 1 ))) // 1 day cache
      {
      $cache = JsonComponent::decode(file_get_contents($cacheFile));
      }
    else
      {
      $items = count(MidasLoader::loadModel('Item')->getAll());
      $users = count(MidasLoader::loadModel('User')->getAll());
      $cache = array( (($items >1)? $items." items":$items." item"),
              (($users >1)? $users." users":$users." user"));
      $modulesReturns = Zend_Registry::get('notifier')->callback("CALLBACK_JOURNAL_GET_STATS", array());
      foreach($modulesReturns as $return)
        {
        $cache = array_merge($cache, $return);
        }      
      file_put_contents($cacheFile, JsonComponent::encode($cache));
      }
    $this->view->footerStats = $cache;    
    }
  
  } //end class
