<?php
require_once BASE_PATH.'/privateModules/reviewosehra/models/base/QuestionlistBase.php';

class Reviewosehra_QuestionlistModel extends Reviewosehra_QuestionlistModelBase
{ 
  /** get All*/
  function getAll()
    {
    $rowset = $this->database->fetchAll($this->database->select()->order(array('name DESC')));
    $results = array();
    foreach($rowset as $row)
      {
      $results[] = $this->initDao('Questionlist', $row, 'reviewosehra');
      }
    return $results;
    }
    
  function getTopics($dao)
    {
    $rowset = $this->database->fetchAll(
            $this->database->select()
            ->setIntegrityCheck(false)
            ->from('reviewosehra_topic')
            ->where('questionlist_id = '.$dao->getKey())
            ->order(array('position ASC')));
    $results = array();
    foreach($rowset as $row)
      {
      $results[] = $this->initDao('Topic', $row, 'reviewosehra');
      }
    return $results;
    }
}  // end class
