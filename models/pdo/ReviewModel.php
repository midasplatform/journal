<?php

require_once BASE_PATH . '/privateModules/reviewosehra/models/base/ReviewBase.php';

class Reviewosehra_ReviewModel extends Reviewosehra_ReviewModelBase 
{
  function getByRevisionAndUser($revision, $user)
    {
    $rowset = $this->database->fetchAll(
            $this->database->select()
                    ->setIntegrityCheck(false)
                    ->from('reviewosehra_review')
                    ->where('revision_id = ' . $revision->getKey())
                    ->where('user_id = ' . $user->getKey()));
    $results = array();
    foreach ($rowset as $row)
      {
      $results[] = $this->initDao('Revision', $row, 'reviewosehra');
      }
    return $results;
    }
    
  function getByRevision($revision)
    {
    $rowset = $this->database->fetchAll(
            $this->database->select()
                    ->setIntegrityCheck(false)
                    ->from('reviewosehra_review')
                    ->where('revision_id = ' . $revision->getKey()));
    $results = array();
    foreach ($rowset as $row)
      {
      $results[] = $this->initDao('Revision', $row, 'reviewosehra');
      }
    return $results;
    }
}

// end class
