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
/** Module configure controller */
class Journal_ConfigController extends Journal_AppController
{
  /** index action */
  function indexAction()
    {
    $this->requireAdminPrivileges();
    $this->view->defaultJournal = MidasLoader::loadModel("Setting")->getValueByName('defaultJournal', "journal");
    $this->view->defaultLayout = MidasLoader::loadModel("Setting")->getValueByName('defaultLayout', "journal");
    $this->view->adminEmail = MidasLoader::loadModel("Setting")->getValueByName('adminEmail', "journal");
    $this->view->baseHandle = MidasLoader::loadModel("Setting")->getValueByName('baseHandle', "journal");
    $this->view->oldWebsiteUrl = MidasLoader::loadModel("Setting")->getValueByName('oldWebsiteUrl', "journal");
    $this->view->licenseDisclaimer = MidasLoader::loadModel("Setting")->getValueByName('licenseDisclaimer', "journal");
    $this->view->captchaSecret = MidasLoader::loadModel("Setting")->getValueByName('captchaSecret', "journal");
    $this->view->json['isConfigSaved'] = 0;

    if($this->_request->isPost() && is_numeric($_POST['defaultJournal']))
      {
      $this->view->json['isConfigSaved'] = 1;
      $this->view->defaultJournal = $_POST['defaultJournal'];
      $this->view->defaultLayout = $_POST['defaultLayout'];
      $this->view->baseHandle = $_POST['baseHandle'];
      $this->view->adminEmail = $_POST['adminEmail'];
      $this->view->oldWebsiteUrl = $_POST['oldWebsiteUrl'];
      $this->view->licenseDisclaimer = $_POST['licenseDisclaimer'];
      $this->view->captchaSecret = $_POST['captchaSecret'];
      MidasLoader::loadModel("Setting")->setConfig('adminEmail', $this->view->adminEmail, "journal");
      MidasLoader::loadModel("Setting")->setConfig('defaultJournal', $this->view->defaultJournal, "journal");
      MidasLoader::loadModel("Setting")->setConfig('defaultLayout', $this->view->defaultLayout, "journal");
      MidasLoader::loadModel("Setting")->setConfig('baseHandle', $this->view->baseHandle, "journal");
      MidasLoader::loadModel("Setting")->setConfig('oldWebsiteUrl', $this->view->oldWebsiteUrl, "journal");
      MidasLoader::loadModel("Setting")->setConfig('licenseDisclaimer', $this->view->licenseDisclaimer, "journal");
      MidasLoader::loadModel("Setting")->setConfig('captchaSecret', $this->view->captchaSecret, "journal");
      if(is_numeric($this->view->baseHandle))
        {
        // This is a hack allowing us to use the Zend dispatch mechanisum to resolve the handles
        $handleControllerFolder = __DIR__."/../../handle/controllers";
        $handleController = $this->view->baseHandle."Controller.php";
        if(!is_writeable($handleControllerFolder))
          {
          throw new Zend_Exception($handleControllerFolder." have to be writable.");
          }
        if(!file_exists($handleControllerFolder."/".$handleController))
          {
          $content = file_get_contents($handleControllerFolder."/TemplateController.php");
          $content = str_replace("Handle_TemplateController", "Handle_".$this->view->baseHandle."Controller", $content);
          file_put_contents($handleControllerFolder."/".$handleController, $content);
          }
        }
      }
    } // end indexAction
    
    
  /** Migrate from midas 2 version of the journal */
  function migrateAction()
    {
    $this->requireAdminPrivileges();
    $this->assetstores = MidasLoader::loadModel("Assetstore")->getAll();

    if($this->getRequest()->isPost())
      {
      $this->disableLayout();
      $this->disableView();
      
      if(empty($_POST['midas2_hostname']) || empty($_POST['midas2_user'])
              || empty($_POST['midas2_assetstore']) || empty($_POST['midas2_database']))
        {
        echo json_encode(array('error' => $this->t('The form is invalid. Missing values.')));
        return false;
        }

      $midas2_hostname = $_POST['midas2_hostname'];
      $midas2_port = $_POST['midas2_port'];
      $midas2_user = $_POST['midas2_user'];
      $midas2_password = $_POST['midas2_password'];
      $midas2_database = $_POST['midas2_database'];
      $midas2_assetstore = $_POST['midas2_assetstore'];
      $midas3_assetstore = $_POST['assetstore'];

      // Check that the assetstore is accessible
      if(!file_exists($midas2_assetstore))
        {
        echo json_encode(array('error' => $this->t('MIDAS2 assetstore is not accessible.')));
        return false;
        }

      // Remove the last slashe if any
      if($midas2_assetstore[strlen($midas2_assetstore) - 1] == '\\'
         || $midas2_assetstore[strlen($midas2_assetstore) - 1] == '/')
        {
        $midas2_assetstore = substr($midas2_assetstore, 0, strlen($midas2_assetstore) - 1);
        }

      $component = MidasLoader::loadComponent("Migration", 'journal');
        
      $component->midas2User = $midas2_user;
      $component->midas2Password = $midas2_password;
      $component->midas2Host = $midas2_hostname;
      $component->midas2Database = $midas2_database;
      $component->midas2Port = $midas2_port;
      $component->midas2Assetstore = $midas2_assetstore;
      $component->assetstoreId = $midas3_assetstore;
      try
        {
        $component->migrate($this->userSession->Dao->getUserId());
        }
      catch(Zend_Exception $e)
        {
        echo json_encode(array('error' => $this->t($e->getMessage())));
        return false;
        }

      echo json_encode(array('message' => $this->t('Migration sucessful.')));
      }

    // Display the form
    }

    
    /** Migrate from midas 2 version of the journal */
  function migrate2Action()
    {
    $this->requireAdminPrivileges();
    $this->disableLayout();
    $this->disableView();
    $this->assetstores = MidasLoader::loadModel("Assetstore")->getAll();


      
    $midas2_hostname = "localhost";
    $midas2_port = 5432;
    $midas2_user = "midas";
    $midas2_password ="1997";
    $midas2_database = "ij";
    $midas2_assetstore = "/docs/WEBS/Midas/IJAssetstore/assetstore/";
    $midas3_assetstore = 1;

    // Check that the assetstore is accessible
    if(!file_exists($midas2_assetstore))
      {
      echo json_encode(array('error' => $this->t('MIDAS2 assetstore is not accessible.')));
      return false;
      }

    // Remove the last slashe if any
    if($midas2_assetstore[strlen($midas2_assetstore) - 1] == '\\'
       || $midas2_assetstore[strlen($midas2_assetstore) - 1] == '/')
      {
      $midas2_assetstore = substr($midas2_assetstore, 0, strlen($midas2_assetstore) - 1);
      }

    $component = MidasLoader::loadComponent("Migration", 'journal');

    $component->midas2User = $midas2_user;
    $component->midas2Password = $midas2_password;
    $component->midas2Host = $midas2_hostname;
    $component->midas2Database = $midas2_database;
    $component->midas2Port = $midas2_port;
    $component->midas2Assetstore = $midas2_assetstore;
    $component->assetstoreId = $midas3_assetstore;
    try
      {
      $component->migrate($this->userSession->Dao->getUserId());
      }
    catch(Zend_Exception $e)
      {
        echo $e->getMessage();
        echo $e->getTraceAsString();
        return false;
      }

    echo json_encode(array('message' => $this->t('Migration sucessful.')));
    }



} // end class
