<?php

class Googleoauth_UserController extends Googleoauth_AppController {

  public function loginAction()
    {
    $this->disableLayout();
    $this->disableView();
    include dirname(__FILE__) . '/../library/TBS/Auth.php';
    include dirname(__FILE__) . '/../library/TBS/Auth/Storage/MultipleIdentities.php';
    include dirname(__FILE__) . '/../library/TBS/Auth/Adapter/Google.php';
    include dirname(__FILE__) . '/../library/TBS/Auth/Identity/Generic.php';
    include dirname(__FILE__) . '/../library/TBS/Auth/Identity/Container.php';
    include dirname(__FILE__) . '/../library/TBS/Auth/Identity/Google.php';
    include dirname(__FILE__) . '/../library/TBS/Resource/Google.php';
    include dirname(__FILE__) . '/../library/TBS/OAuth2/Consumer.php';

    $auth = TBS\Auth::getInstance();

    // Here the response of the providers are registered
    if ($this->_hasParam('code'))
      {
      $adapter = new TBS\Auth\Adapter\Google(
                      $this->_getParam('code'));
      $result = $auth->authenticate($adapter);
      if (isset($result) && $result->isValid())
        {
        $provider = $result->getIdentity();
        $profile = $provider->getApi()->getProfile();
        $email = $profile['email'];
        $firstname = $profile['given_name'];
        $lastname = $profile['family_name'];
        $userDao = MidasLoader::loadModel("User")->getByEmail($email);
        if($userDao === false)
          {
          $password = uniqid();
          $userDao = MidasLoader::loadModel("User")->createUser(trim($email), 
                  $password, 
                  trim($firstname), 
                  trim($lastname));
          }

          
        setcookie('midasUtil', null, time() + 60 * 60 * 24 * 30, '/'); //30 days
        Zend_Session::start();
        $user = new Zend_Session_Namespace('Auth_User');
        $user->setExpirationSeconds(60 * Zend_Registry::get('configGlobal')->session->lifetime);
        $user->Dao = $userDao;
        $user->lock();
        
        $this->_redirect("/");
        }
      else
        {
        echo "Error";
        }
      }
    else
      { // Normal login page          
      // We only use google
      $this->_redirect(TBS\Auth\Adapter\Google::getAuthorizationUrl());
      }
    }
}
