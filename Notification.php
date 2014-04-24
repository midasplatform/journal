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

require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';
class Journal_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'journal';
  public $_moduleComponents=array('Api');

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    $this->addCallBack('CALLBACK_CORE_GET_CONFIG_TABS', 'getConfigTabs');
    $this->addCallBack('CALLBACK_CORE_AUTHENTICATION', 'authIntercept');
    }//end init
    
    
  /**
   * The goal is to convert old account to new ones
   */
  public function authIntercept($params)
    {
    $email = $params['email'];
    $password = $params['password'];
    
    $userDao = MidasLoader::loadModel("User")->getByEmail($email);
    if($userDao && $userDao->getSalt() == md5($password))
      {
      MidasLoader::loadModel("User")->convertLegacyPasswordHash($userDao, $password);
      }
    }

   /** get Config Tabs */
  public function getConfigTabs($params)
    {
    $user = $params['user'];
    $fc = Zend_Controller_Front::getInstance();
    $webroot = $fc->getBaseUrl();
    return array('Notification' => $webroot.'/journal/user/notification?userId='.$user->getKey());
    }
 
  } //end class
?>



 
