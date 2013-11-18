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
class Journal_LayoutComponent extends AppComponent
{  
  /**
   * Get the name of the selected layout
   * @return string
   */
  public function getLayoutName()
    {    
    $modulesConfig = Zend_Registry::get('configsModules');  
    return $modulesConfig['journal']->layout;
    }
    
  /**
   * Get the url of the main log (RSS feed)
   * @return string
   */
  public function getLogoUrl()
    {    
    $modulesConfig = Zend_Registry::get('configsModules');  
    $fc = Zend_Controller_Front::getInstance();

    if($modulesConfig['journal']->layout == "ij")
      {
      return UtilityComponent::getServerURL().$fc->getBaseUrl()."/privateModules/journal/public/images/logo.png";
      }
    else if($modulesConfig['journal']->layout == "osehra")
      {
      return UtilityComponent::getServerURL().$fc->getBaseUrl()."/privateModules/journal/public/images/osehra/logo.png";
      }
    else
      {
      return "";
      }
    }
} // end class