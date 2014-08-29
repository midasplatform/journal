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

class Journal_HelpController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();
    }

  /** Index. Main help page */
  function indexAction()
    {
     $this->view->title .= ' - Help';
    $settingModel = MidasLoader::loadModel('Setting');
    try
      {
      $this->view->content = $settingModel->getValueByName('help_text', 'journal');
      }
    catch(Exception $e)
      {
      $this->view->content = "";
      }
    }

  /** About the website*/
  function aboutAction()
    {
     $this->view->title .= ' - About';
    $settingModel = MidasLoader::loadModel('Setting');
    try
      {
      $this->view->content = $settingModel->getValueByName('about_text', 'journal');
      }
    catch(Exception $e)
      {
      $this->view->content = "";
      }
    }

  /** Frequent ask questions */
  function faqAction()
    {
     $this->view->title .= ' - FAQ';
    $settingModel = MidasLoader::loadModel('Setting');
    try
      {
      $this->view->content = $settingModel->getValueByName('faq_text', 'journal');
      }
    catch(Exception $e)
      {
      $this->view->content = "";
      }
    }


  /** Send a feedback */
  function feedbackAction()
    {
    $this->view->time = time();
    $forms = array();
    if($this->logged)
      {
      $forms['email'] = $this->userSession->Dao->getEmail();
      }
    else
      {
      $forms['email']='';
      }
    $forms['where']='';
    $forms['what']='';
    $this->view->error = "";

    if($this->_request->isPost())
      {
      $forms['email'] = $_POST['email'];
      $forms['where'] = $_POST['where'];
      $forms['what'] = $_POST['what'];
      if((time()-$_POST['timer'])<5)
        {
        $this->view->forms = $forms;
        $this->view->error = 'Hello M Robot (Wait 5 seconds and re submit).';
        return;
        }
      if(!empty($_POST['name'])||!empty($_POST['mail'])||!empty($_POST['age']))
        {
        $this->view->forms = $forms;
        $this->view->error = 'Hello M Robot.';
        return;
        }

      $adminEmail = MidasLoader::loadModel("Setting")->getValueByName('adminEmail', "journal");

      $headers  = "From: ".$adminEmail."\n";
      $from = $forms['email'];
      $message  = Zend_Registry::get('configGlobal')->application->name." Feedback from ".trim($from)."\n\n";
      $message .= "Where: ".$forms['where']."\n";
      $message .= "What happened: ".$forms['what']."\n\n";
      $message .= "System: ".$_SERVER["HTTP_USER_AGENT"]."\n";

      if(strpos($forms['what'], "<a href"))
        {
        echo "Please do not send URL in the  problem text area.";
        exit;
        }

      if(mail($adminEmail, Zend_Registry::get('configGlobal')->application->name, " Feedback", $message, $headers))
        {
        $this->view->error = '<br/>Feedback sent successfully. We will get back to you shortly.<br/>';
        $this->view->sent = 1;
        }
      else
        {
        $this->view->error = 'Message Error.';
        }
      }
    $this->view->forms = $forms;
    }
}//end class