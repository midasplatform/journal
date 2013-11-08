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

class Journal_UserCoreController extends Journal_AppController
{
  function settingsAction()
    {    
    $request = $this->getRequest();
    $response = $this->getResponse();
    include_once BASE_PATH.'/core/controllers/UserController.php';
    $name = "UserController";
    $controller = new $name($request, $response);
    $controller->userSession = $this->userSession;
    $controller->logged = $this->logged;
    $actionName = 'settingsAction';
    $controller->$actionName();
    if($this->_request->isPost())
      {
      $this->disableView();
      }
    else
      {
      $this->view->setScriptPath(BASE_PATH."/core/views");
      $this->render('user/settings', null, true);
      }
    } // end method indexAction
 
}//end class
