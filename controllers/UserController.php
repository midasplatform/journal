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

class Journal_UserController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
    
  // Show setting
  function settingsAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception("You have to be logged in.");
      }
    $this->view->userId = $this->_getParam('userId');
    }
    
  // Show notification setting
  function notificationAction()
    {    
    if(!$this->logged)
      {
      throw new Zend_Exception("You have to be logged in.");
      }
      
    if(isset($_POST) && !empty($_POST))
      {
      MidasLoader::loadComponent("Notification", "journal")->setUserNotificationStatus($this->userSession->Dao, 
              $_POST['NewSubmissionEmail'], $_POST['NewReviewsEmail']);
      $this->disableView();
      echo JsonComponent::encode(array(1, "Changes saved."));
      }
      
    $this->view->status = MidasLoader::loadComponent("Notification", "journal")->getUserNotificationStatus($this->userSession->Dao);
    $this->disableLayout();
    }
  
  /** Register a new user */
  function registerAction()
    {

    }      
}//end class