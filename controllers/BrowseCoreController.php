<?php
/*=========================================================================
 MIDAS Server
 Copyright (c) Kitware SAS. 26 rue Louis Guérin. 69100 Villeurbanne, FRANCE
 All rights reserved.
 More information http://www.kitware.com

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/
/** demo overwrite component */
class Journal_BrowseCoreController extends Journal_AppController
{
  function publicationAction()
    {
    $id = array_pop(explode('/',$_SERVER['REQUEST_URI']));
    if(is_numeric($id))
      {
      $item = MidasLoader::loadComponent("Migration", "journal")->getItemByAllId($id);
      if($item)
        {
        $revision = MidasLoader::loadModel("Item")->getLastRevision($item);
        $this->_redirect("/journal/view/".$revision->getKey());
        }
      }
    throw new Zend_Exception('Unable to find content.');
    }
  function indexAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    } // end method indexAction
    
  function movecopyAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
  function selectitemAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
  function selectfolderAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
  function getfolderscontentAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
  function getfolderssizeAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
  function getelementinfoAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
  function deleteAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->callCoreAction();
    }
}//end class
