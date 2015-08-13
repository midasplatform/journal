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
    $layout = MidasLoader::loadModel("Setting")->getValueByName('defaultLayout', "journal");

    if($layout == null) 
      {
      $fc = Zend_Controller_Front::getInstance();
      echo 'Please set the Journal Module configuration <a href="'.$fc->getBaseUrl().'/journal/config">here</a>';
      exit;
      }
    return $layout;
    }
    
  /**
   * Get the url of the main log (RSS feed)
   * @return string
   */
  public function getLogoUrl()
    {    
    if($this == null) return "";
    $layout = $this->getLayoutName();
    $fc = Zend_Controller_Front::getInstance();

    if($layout == "ij")
      {
      return UtilityComponent::getServerURL().$fc->getBaseUrl()."/privateModules/journal/public/images/logo.png";
      }
    else if($layout == "osehra")
      {
      return UtilityComponent::getServerURL().$fc->getBaseUrl()."/privateModules/journal/public/images/osehra/logo.png";
      }
    else
      {
      return "";
      }
    }
} // end class