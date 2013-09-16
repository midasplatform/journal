<?php
abstract class Journal_IssueModelBase extends Journal_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'journal_folder';
    $this->_key = 'folder_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'folder_id' =>  array('type' => MIDAS_DATA),
      
      );
    $this->initialize(); // required
    } // end __construct()

    
} // end class Validation_DashboardModelBase
