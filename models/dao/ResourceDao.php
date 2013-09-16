<?php
/**
 * Resource DAO
 * Extend the default Item methods
 */
Zend_Loader::loadClass("ItemDao", BASE_PATH.'/core/models/dao');

class Journal_ResourceDao extends ItemDao
  {
  public $_model = 'Item';
  
  public $_associatedResources = false;
  public $_metadata = false;
  public $_revision = false;
  
  /** Constructor */
  public function __construct()
    {
    parent::__construct();
    $this->setName('');
    $this->setDescription('');
    $this->setType(RESOURCE_TYPE_NOT_DEFINED);
    }
 
  /**
   * The goal of this method is to enable the resource.
   * The user will then be able to find it using the search and filter (based on solr)
   */
  function enable()
    {
    $this->setMetaDataByQualifier("enable", "true");
    $folder = end($this->getFolders());
    $community = MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($folder));
    $this->setMetaDataByQualifier("issue", $folder->getKey());
    $this->setMetaDataByQualifier("community", $community->getKey());
    }
    
   /** Get Categories
   * 
   * @return array of ids
   */
  function getCategories()
    {
    $metadata = $this->getMetaDataByQualifier("categories");
    if(!$metadata) return array();
    return explode(" ", $metadata->getValue());
    }

  /* Set Catgproes
   * @param array $categories Array of ids
   */
  function setCategories($categories)
    {
    if(is_array($categories))$this->setMetaDataByQualifier("categories", join(' ', $categories));
    }
    
   /** Get Authors
   * 
   * @return array of names
   */
  function getAuthors()
    {
    $metadata = $this->getMetaDataByQualifier("authors");
    if(!$metadata) return array();
    $authors = explode(" ;;; ", $metadata->getValue());
    foreach($authors as &$author) $author = explode(" --- ", $author);
    return $authors;
    }
   /** Get Authors
   * 
   * @return array of names
   */
  function getAuthorsFullNames()
    {
    $metadata = $this->getMetaDataByQualifier("authors");
    if(!$metadata) return array();
    $authors = explode(" ;;; ", $metadata->getValue());
    foreach($authors as &$author) $author = str_replace(" --- ", ' ' , $author);
    return $authors;
    }

  /* Set Authors
   * @param array $categories Array of ids
   */
  function setAuthors($authors)
    {
    $authorsArray = array();
    foreach($authors[0] as $key => $author)
      {
      if(!empty($authors[0][$key]) && !empty($authors[1][$key]))$authorsArray[] = $authors[0][$key]." --- ".$authors[1][$key];    
      }
    $this->setMetaDataByQualifier("authors", join(" ;;; ", $authorsArray));
    }
    
  /** Get Tags
   * 
   * @return array of string
   */
  function getTags()
    {
    $metadata = $this->getMetaDataByQualifier("tags");
    if(!$metadata) return array();
    return explode(" --- ", $metadata->getValue());
    }

   /* Set Tags
   * @param array $tags Array of values
   */
  function setTags($tags)
    {
    if(isset($tags))$this->setMetaDataByQualifier("tags", join(' --- ', array_filter($tags)));
    }
    
  /** Get Institution
   * 
   * @return 
   */
  function getInstitution()
    {
    $metadata = $this->getMetaDataByQualifier("insitution");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Institution
   * @param 
   */
  function setInstitution($institution)
    {
    $this->setMetaDataByQualifier("insitution", $institution);
    }
    
    
  /**
   * Get Revision
   * @return ItemRevision
   */
  function getRevision()
    {
    if(!$this->_revision)  $this->_revision = $this->getModel()->getLastRevision($this);
    return $this->_revision;
    }
    
  /**
   * Set Revision
   * @param ItemRevision
   */
  function setRevision($revision)
    {
    $this->_metadata = false;
    if($revision == "New")
      {
      Zend_Loader::loadClass('ItemRevisionDao', BASE_PATH.'/core/models/dao');
      $revision = new ItemRevisionDao();
      $revision->setChanges("");
      if(Zend_Registry::get('userSession') != null)
        {
        $revision->setUser_id(Zend_Registry::get('userSession')->Dao->getKey());
        }      
      $revision->setDate(date('c'));
      $revision->setLicenseId(null);
      $lastRevision = false;
      if($this->saved) $lastRevision = $this->getModel()->getLastRevision($this);
      if(!$lastRevision)$this->_metadata = array();
      else $this->_metadata = MidasLoader::loadModel('ItemRevision')->getMetadata($lastRevision);
      }
    $this->_revision = $revision;    
    }
    
  /**
   * Get Metadata object
   * @param type $type
   * @return type
   */
  private function getMetaDataByQualifier($type)
    {
    $metadata = $this->getMetadata();
    foreach($metadata as $m)
      {
      if($m->getQualifier() == $type)
        {
        return $m;
        }
      }
    return false;
    }   
  /**
   * Save metadata value
   * @param string $type
   * @param string $value
   * @return MetadataDao
   */
  private function setMetaDataByQualifier($type, $value)
    {
    // Gets the metadata
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", $type);
    if(!$metadataDao)  $metadataDao= MidasLoader::loadModel('Metadata')->addMetadata(MIDAS_METADATA_TEXT, "journal", $type, "");
    $metadataDao->setItemrevisionId($this->getRevision()->getKey());
    $metadataDao->setValue($value);
    return MidasLoader::loadModel('Metadata')->saveMetadataValue($metadataDao);  
    }   
        
  /**
   * Return all the item matadata (categories, keywords...)
   * @return array of metadata
   */
  private function getMetadata()
    {
    if($this->_metadata !== false) return $this->_metadata;
    if(!$this->saved) return array();
    $this->_metadata = MidasLoader::loadModel('ItemRevision')->getMetadata($this->getRevision());
    return $this->_metadata;
    }
  }
