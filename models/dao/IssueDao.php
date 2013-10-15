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

/**
 * Resource DAO
 * Extend the default Item methods
 */
Zend_Loader::loadClass("FolderDao", BASE_PATH.'/core/models/dao');

class Journal_IssueDao extends FolderDao
  {
  public $_model = 'Folder';
  public $initialized = false;
  public $creation_date = "";
  public $paperdue_date = "";
  public $decision_date = "";
  public $publication_date = "";
  public $logo = "";
  public $short_description = "";
  public $introductory_text = "";
  public $authorLicense = "";
  public $readerLicense = "";
  public $related_link = "";
  public $defaultpolicy = "";
  
  /** Constructor */
  public function __construct()
    {
    parent::__construct();
    $this->setName('');
    $this->setDescription('');
    }
    
  /**
   * Set the dao values based on a folder dao.
   */
  function initValues()
    {
    if(!$this->initialized && $this->saved)
      {
      $db = Zend_Registry::get('dbAdapter');
      $sql = $db->select()
            ->from('journal_folder')
            ->where('folder_id = '.$this->folder_id) ;
      $this->initialized = true;
      $row = $db->fetchRow($sql);
      if($row)
        {
        foreach($row as $key => $r)
          {
          if(isset($this->{$key}) && $key != "folder_id")
            {
            $this->{$key} = $r;
            }
          }
        }
      else
        {
        $db->query("INSERT INTO journal_folder (folder_id) VALUES ('".$this->getKey()."')");
        }
      }
    }
  
  /**
   * Custom save method.
   */
  function save()
    {
    $return = MidasLoader::loadModel("Folder")->save($this);
    $this->initValues();
    $data = array(
      'paperdue_date'      => $this->paperdue_date,
      'decision_date'      => $this->decision_date,
      'publication_date'      => $this->publication_date,
      'logo'      => $this->logo,
      'short_description'      => $this->short_description,
      'introductory_text'      => $this->introductory_text,
      'related_link'      => $this->related_link,
      'authorLicense'      => $this->authorLicense,
      'readerLicense'      => $this->readerLicense,
      'defaultpolicy'      => $this->defaultpolicy
      );
    $db = Zend_Registry::get('dbAdapter');
    $db->update('journal_folder', $data, 'folder_id = '.$this->getKey());
    return $return;
    }
    
  /**
   * Define getter and setters.
   */
  function getPaperdueDate(){$this->initValues();return $this->paperdue_date;}
  function getDefaultPolicy(){$this->initValues();return $this->defaultpolicy;}
  function getDecisionDate(){$this->initValues();return $this->decision_date;}
  function getPublicationDate(){$this->initValues();return $this->publication_date;}
  function getLogo(){$this->initValues();return $this->logo;}
  function getShortDescription(){$this->initValues();return $this->short_description;}
  function getIntroductoryText(){$this->initValues();return $this->introductory_text;}
  function getRelatedLink(){$this->initValues();return $this->related_link;}
  function getAuthorLicense(){$this->initValues();return $this->authorLicense;}
  function getRedearLicense(){$this->initValues();return $this->readerLicense;}
  
  function setPaperdueDate($v){$this->initValues();$this->paperdue_date = $v;}
  function setDefaultPolicy($v){$this->initValues();$this->defaultpolicy = $v;}
  function setRelatedLink($v){$this->initValues();$this->related_link = $v;}
  function setDecisionDate($v){$this->initValues();$this->decision_date = $v;}
  function setPublicationDate($v){$this->initValues();$this->publication_date = $v;}
  function setLogo($v){$this->initValues();return $this->logo = $v;}
  function setShortDescription($v){$this->initValues();$this->short_description = $v;}
  function setIntroductoryText($v){$this->initValues();$this->introductory_text = $v;}
  function setAuthorLicense($v){$this->initValues();$this->authorLicense = $v;}
  function setRedearLicense($v){$this->initValues();$this->readerLicense = $v;}
  }

  