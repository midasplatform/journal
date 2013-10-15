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
