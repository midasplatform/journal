<?php
require_once BASE_PATH.'/privateModules/reviewosehra/models/base/TopicBase.php';

class Reviewosehra_TopicModel extends Reviewosehra_TopicModelBase
{
 
  function getQuestions($dao)
    {
    $rowset = $this->database->fetchAll(
            $this->database->select()
            ->setIntegrityCheck(false)
            ->from('reviewosehra_question')
            ->where('topic_id = '.$dao->getKey())
            ->order(array('position ASC')));
    $results = array();
    foreach($rowset as $row)
      {
      $results[] = $this->initDao('Question', $row, 'reviewosehra');
      }
    return $results;
    }
}  // end class
