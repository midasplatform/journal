<?php

class Journal_IndexCoreController extends Journal_AppController
{
  /**
   * @method initAction()
   *  Index Action (first action when we access the application)
   */
  function init()
    {
    } // end method indexAction

  /** index action*/
  function indexAction()
    {
    $this->_redirect("/journal");
    }

}//end class
