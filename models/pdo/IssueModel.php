<?php
require_once BASE_PATH.'/privateModules/journal/models/base/IssueBase.php';

class Journal_IssueModel extends Journal_IssueModelBase
{
  public function findActiveIssues()
    {    
    $rowset = $this->database->fetchAll($this->database->select()
            ->where('paperdue_date < \''.date('c').'\''));
    $results = array();
    foreach($rowset as $row)
      {
      $folder = MidasLoader::loadModel("Folder")->load($row['folder_id']);
      $results[] = MidasLoader::loadModel("Folder")->initDao("Issue", $folder->toArray(), "journal");
      }
    return $results;
    }
    
}  // end class
