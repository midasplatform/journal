<?php
abstract class Reviewosehra_QuestionlistModelBase extends Reviewosehra_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'reviewosehra_questionlist';
    $this->_key = 'questionlist_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'questionlist_id' =>  array('type' => MIDAS_DATA),
        'category_id' => array('type' => MIDAS_DATA),
        'name' => array('type' => MIDAS_DATA),
        'description' => array('type' => MIDAS_DATA),   
        'type' => array('type' => MIDAS_DATA),
        'topics' => array('type' => MIDAS_ONE_TO_MANY, 'model' => 'Topic', 'module' => "reviewosehra", 'parent_column' => 'questionlist_id', 'child_column' => 'questionlist_id'),
      );
    $this->initialize(); // required
    } // end __construct()  
    
  public function delete($dao)
    {
    if(!$dao instanceof Reviewosehra_QuestionlistDao)
      {
      throw new Zend_Exception("Deleting a list requires a valid instance of an Reviewosehra_QuestionlistDao.");
      }
    $topics = $dao->getTopics();
 
    foreach($topics as $topic)
      {
      MidasLoader::loadModel('Topic', 'reviewosehra')->delete($topic);
      }
    parent::delete($dao);
    }// delete
} // end class Validation_DashboardModelBase
