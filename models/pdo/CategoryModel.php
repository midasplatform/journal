<?php
require_once BASE_PATH.'/privateModules/journal/models/base/CategoryBase.php';

class Journal_CategoryModel extends Journal_CategoryModelBase
{
  /** 
   * Get all the categories*
   * @return array
   */
  function getAll()
    {
    $rowset = $this->database->fetchAll($this->database->select());
    $results = array();
    foreach($rowset as $row)
      {
      $results[] = $this->initDao('Category', $row, 'journal');
      }
    return $results;
    }

}  // end class
