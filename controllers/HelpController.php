<?php

class Journal_HelpController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();    
    }
    
  /** Index */
  function indexAction()
    {
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
    
  function aboutAction()
    {
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

    
  function faqAction()
    {
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
   
  
  /** Feedback */
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
        
      $modulesConfig = Zend_Registry::get('configsModules');  
      $adminEmail = $modulesConfig['journal']->adminemail;
        
      $headers  = "From: ".$adminEmail."\n";
      $from = $forms['email'];
      $message  = Zend_Registry::get('configGlobal')->application->name." Feedback from ".trim($from)."\n\n";
      $message .= "Where: ".$forms['where']."\n";
      $message .= "What happened: ".$forms['what']."\n\n";
      $message .= "System: ".$_SERVER["HTTP_USER_AGENT"]."\n";
      
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