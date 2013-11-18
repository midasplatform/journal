<?php
abstract class Reviewosehra_ReviewModelBase extends Reviewosehra_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'reviewosehra_review';
    $this->_key = 'review_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'review_id' =>  array('type' => MIDAS_DATA),
        'revision_id' => array('type' => MIDAS_DATA),
        'user_id' => array('type' => MIDAS_DATA),
        'type' => array('type' => MIDAS_DATA),
        'cache_summary' => array('type' => MIDAS_DATA),
        'content' => array('type' => MIDAS_DATA),
        'complete' => array('type' => MIDAS_DATA),
        'user' => array('type' => MIDAS_MANY_TO_ONE, 'model' => 'User', 'parent_column' => 'user_id', 'child_column' => 'user_id')
      );
    $this->initialize(); // required
    } // end __construct()    
} // end class Validation_DashboardModelBase
