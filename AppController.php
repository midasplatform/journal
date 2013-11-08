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

    parent::preDispatch();
    
    // Select the module's layout
    $this->_helper->layout->setLayoutPath(dirname(__FILE__)."/layouts");
    $this->_helper->layout->setLayout(MidasLoader::loadComponent("Layout", 'journal')->getLayoutName());
    $this->view->json['dynamicHelp'] = array();
    }
  
  } //end class
?>