<?php
abstract class Journal_CategoryModelBase extends Journal_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'journal_category';
    $this->_key = 'category_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'category_id' =>  array('type' => MIDAS_DATA),
        'parent_id' => array('type' => MIDAS_DATA),
        'name' => array('type' => MIDAS_DATA),
        'parent' =>  array('type' => MIDAS_MANY_TO_ONE,
                          'module' => 'journal',
                          'model' => 'Category',
                          'parent_column' => 'parent_id',
                          'child_column' => 'category_id'),
        'children' =>  array('type' => MIDAS_ONE_TO_MANY,
                          'module' => 'journal',
                          'model' => 'Category',
                          'parent_column' => 'category_id',
                          'child_column' => 'parent_id')   
      );
    $this->initialize(); // required
    } // end __construct()
  
  abstract function getAll();
    
  /**
   * Delete a category and its children 
   * @param journal_CategoryDao $dao
   * @throws Zend_Exception
   */
  public function delete($dao)
    {
    if(!$dao instanceof journal_CategoryDao)
      {
      throw new Zend_Exception('You must pass a category');
      }   
    $children = $dao->getChildren();
    foreach($children as $child)
      {
      $this->delete($child);
      }      
    parent::delete($dao);
    }// delete
    
} // end class Validation_DashboardModelBase
