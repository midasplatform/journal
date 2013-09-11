<?php
namespace TBS\Auth\Adapter;

use \TBS\Auth\Identity\Google as Identity;
use \TBS\OAuth2\Consumer;

use \Zend_Auth_Result as Result;
use \Zend_Registry as Registry;

class Google implements \Zend_Auth_Adapter_Interface
{
   protected $_accessToken;
   protected $_requestToken;
   protected $_options;
 
   public function __construct($requestToken)
   {
      $this->_setOptions();
      $this->_setRequestToken($requestToken);
   }
 
   public function authenticate()
   {
      $result = array();
      $result['code'] = Result::FAILURE;
      $result['identity'] = NULL;
      $result['messages'] = array();
 
      if(!array_key_exists('error',$this->_accessToken)) {
         $result['code'] = Result::SUCCESS;
         $result['identity'] = new Identity($this->_accessToken);
      }
 
      return new Result($result['code'],
                                  $result['identity'],
                                  $result['messages']);
   }
 
   public static function getAuthorizationUrl()
   {
      $modulesConfig = Registry::get('configsModules');
      $options = $modulesConfig['googleoauth']->google->toArray();
      return Consumer::getAuthorizationUrl($options);
   }
 
   protected function _setRequestToken($requestToken)
   {
      $this->_options['code'] = $requestToken;
      
      $accesstoken = Consumer::getAccessToken($this->_options);

      $accesstoken['timestamp'] = time();
      $this->_accessToken = $accesstoken;
   }
 
   protected function _setOptions($options = null)
   {
      $modulesConfig = Registry::get('configsModules');
      $options = $modulesConfig['googleoauth']->google->toArray();
      $this->_options = $options;
   }
}
