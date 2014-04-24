<?php
/*=========================================================================
 MIDAS Server
 Copyright (c) Kitware SAS. 26 rue Louis Guérin. 69100 Villeurbanne, FRANCE
 All rights reserved.
 More information http://www.kitware.com

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/

require_once BASE_PATH.'/core/models/dao/ItemRevisionDao.php';
require_once BASE_PATH.'/core/models/dao/BitstreamDao.php';
require_once BASE_PATH.'/core/models/dao/ItemDao.php';
require_once BASE_PATH.'/core/models/dao/MetadataDao.php';
require_once BASE_PATH.'/core/models/dao/AssetstoreDao.php';
require_once BASE_PATH.'/core/controllers/components/UploadComponent.php';

define("MIDAS2_RESOURCE_BITSTREAM", 0);
define("MIDAS2_RESOURCE_BUNDLE", 1);
define("MIDAS2_RESOURCE_ITEM", 2);
define("MIDAS2_RESOURCE_COLLECTION", 3);
define("MIDAS2_RESOURCE_COMMUNITY", 4);
define("MIDAS2_POLICY_READ", 0);
define("MIDAS2_POLICY_WRITE", 1);
define("MIDAS2_POLICY_DELETE", 2);
define("MIDAS2_POLICY_ADD", 3);
define("MIDAS2_POLICY_REMOVE", 4);

/** Migration tool*/
class Journal_MigrationComponent extends AppComponent
{
  /** These variables should be set by the UI */
  var $midas2User = "midas";
  var $midas2Password = "midas";
  var $midas2Host = "localhost";
  var $midas2Database = "midas";
  var $midas2Port = "5432";
  var $midas2Assetstore = "C:/xampp/midas/assetstore"; // without end slash
  var $assetstoreId = '1';
  var $epersonToUser = array();
  var $ijuserToUser = array();
  var $categoriesReference = array();
  var $tookitReference = array();

  /** Private variables */
  var $userId;

  /** function to create the items */
  private function _createFolderForItem($collectionId, $parentFolderid)
    {
    $anonymousGroup = MidasLoader::loadModel("Group")->load(MIDAS_GROUP_ANONYMOUS_KEY);
    $Folder = MidasLoader::loadModel("Folder");
    $Bitstream = MidasLoader::loadModel("Bitstream");
    $Item = MidasLoader::loadModel("Item");
    $ItemRevision = MidasLoader::loadModel("ItemRevision");
    $Group = MidasLoader::loadModel("Group");
    $Assetstore = MidasLoader::loadModel("Assetstore");
    $Folderpolicygroup = MidasLoader::loadModel("Folderpolicygroup");
    $Folderpolicyuser = MidasLoader::loadModel("Folderpolicyuser");
    $Itempolicygroup = MidasLoader::loadModel("Itempolicygroup");
    $Itempolicyuser = MidasLoader::loadModel("Itempolicyuser");
    $User = MidasLoader::loadModel("User");

    $colquery = pg_query("SELECT i.item_id, mtitle.text_value AS title, mabstract.text_value AS abstract ".
                         "FROM item AS i ".
                         "LEFT JOIN metadatavalue AS mtitle ON (i.item_id = mtitle.item_id AND mtitle.metadata_field_id = 64) ".
                         "LEFT JOIN metadatavalue AS mabstract ON (i.item_id = mabstract.item_id AND mabstract.metadata_field_id = 27) ".
                         "WHERE i.owning_collection=".$collectionId);
    while($colquery_array = pg_fetch_array($colquery))
      {
      $item_id = $colquery_array['item_id'];
      $title = $colquery_array['title'];
      
      // Add IJ information
      $sql = pg_query("SELECT * FROM isj_publication WHERE itemid=".$item_id);
      $ij_publicationArray = pg_fetch_assoc($sql);
      if(empty($ij_publicationArray)) continue;
      $publicationId = $ij_publicationArray['id'];
      if(!isset($this->ijuserToUser[$ij_publicationArray['authorid']]))continue;
      $authorid = $this->ijuserToUser[$ij_publicationArray['authorid']];
      $author = MidasLoader::loadModel('User')->load($authorid);
      
      $publicationAuthorList = array();
      $sqlAuthors = pg_query("SELECT metadata_value_id,text_value FROM metadatavalue
                                  WHERE item_id='$item_id' AND metadata_field_id='3' ORDER BY place ASC");
      while($ij_authorsArray = pg_fetch_array($sqlAuthors))
        {
        $value = $ij_authorsArray["text_value"];
        if (strpos($value, ",") !== FALSE)
          {
          $firstname = substr($value, strpos($value, ",")+2, strlen($value)-strpos($value, ",")-2);
          $lastname = substr($value, 0, strpos($value, ","));
          }
        else
          {
          $firstname = "";
          $lastname = $value;
          }
          
        $publicationAuthorList[0][] = $firstname;
        $publicationAuthorList[1][] = $lastname;
        }
        
      $publicationCat = array();
      $sqlCat = pg_query("SELECT * FROM isj_publication2category where publicationid=".$publicationId);
      while($ij_cattArray = pg_fetch_array($sqlCat))
        {
        $value = $ij_cattArray["categoryid"];
        if(isset($this->categoriesReference[$value]))
          {
          $publicationCat[] = $this->categoriesReference[$value];
          }
        }
      
      $sqlToolkit = pg_query("SELECT * FROM isj_publication2toolkit where publicationid=".$publicationId);
      while($ij_toolkitArray = pg_fetch_array($sqlToolkit))
        {
        $value = $ij_toolkitArray["toolkitid"];
        if(isset($this->tookitReference[$value]))
          {
          $publicationCat[] = $this->tookitReference[$value];
          }
        }     
        
      $sqlTags = pg_query("SELECT text_value FROM metadatavalue WHERE item_id='$item_id' AND metadata_field_id='57'
                                    ORDER BY place ASC");
      $tags = array();
      while($tagstArray = pg_fetch_array($sqlTags))
        {
        $tags[] = $ij_toolkitArray["text_value"];       
        }      
      
      $institution = $ij_publicationArray['institution'];

      // If title is empty we skip this item
      if(empty($title))
        {
        continue;
        }
        
      $views = 0;
      $downloads = 0;
      $handle = "";
      $grant = "";
        
      $sql = pg_query("SELECT sum(downloads) FROM isj_publication_statistics where publication=".$publicationId);
      $returnArray = pg_fetch_assoc($sql);
      if(isset($returnArray['sum'])) $downloads = $returnArray['sum'];
      $sql = pg_query("SELECT * FROM isj_publication_views where publication=".$publicationId);
      $returnArray = pg_fetch_assoc($sql);
      if(isset($returnArray['views'])) $views = $returnArray['views'];
      $sql = pg_query("SELECT handle FROM handle WHERE resource_id='$item_id' AND resource_type_id='2'");
      $returnArray = pg_fetch_assoc($sql);
      if(isset($returnArray['handle'])) $handle = $returnArray['handle'];
      $sql = pg_query("SELECT text_value FROM metadatavalue WHERE item_id='$item_id' AND metadata_field_id='24'");
      $returnArray = pg_fetch_assoc($sql);
      if(isset($returnArray['text_value'])) $grant = $returnArray['text_value'];

      $abstract = $colquery_array['abstract'];
      $parentFolder = $Folder->load($parentFolderid);    
      
      $resourceDao = MidasLoader::newDao('ResourceDao', 'journal');
      $resourceDao->setRevision("New");
      
      $resourceDao->setView($views);
      $resourceDao->setDownload($downloads);
      $this->getLogger()->warn("---- Creating " .$title);
      $resourceDao->setName($title);
      $resourceDao->setDescription($abstract);
      $resourceDao->setType(RESOURCE_TYPE_PUBLICATION);   
      MidasLoader::loadModel("Item")->save($resourceDao);
      
      MidasLoader::loadModel("Folder")->addItem($parentFolder, $resourceDao);
      
      $adminGroup = $resourceDao->getAdminGroup();
      $memberGroup = $resourceDao->getMemberGroup();
      MidasLoader::loadModel("Itempolicygroup")->createPolicy($adminGroup, $resourceDao, MIDAS_POLICY_ADMIN);
      MidasLoader::loadModel("Itempolicygroup")->createPolicy($memberGroup, $resourceDao, MIDAS_POLICY_READ);
      MidasLoader::loadModel("Itempolicyuser")->createPolicy($author, $resourceDao, MIDAS_POLICY_WRITE);
      
      $policies = $parentFolder->getFolderpolicygroup();
      foreach($policies as $policy)
        {
        if($policy->getPolicy() == MIDAS_POLICY_ADMIN)
          {
          MidasLoader::loadModel("Itempolicygroup")->createPolicy($policy->getGroup(), $resourceDao, MIDAS_POLICY_ADMIN);
          }
        }
        
      // Create the item from the bitstreams
      $bitquery = pg_query("SELECT bundle2bitstream.bitstream_id, bitstream.name, bitstream.internal_id, bitstream.type, bitstream.description
        FROM bundle2bitstream,item2bundle,bitstream
                                WHERE item2bundle.item_id='$item_id'
                                AND item2bundle.bundle_id=bundle2bitstream.bundle_id AND
                                bitstream.bitstream_id=bundle2bitstream.bitstream_id");
      
      $bitstreams = array();

      while($bitquery_array = pg_fetch_array($bitquery))
        {
        $filename = $bitquery_array['name'];
        $internal_id = $bitquery_array['internal_id'];
        $type = $bitquery_array['type'];
        $filepath = $this->midas2Assetstore.'/';
        $filepath .= substr($internal_id, 0, 2).'/';
        $filepath .= substr($internal_id, 2, 2).'/';
        $filepath .= substr($internal_id, 4, 2).'/';
        $filepath .= $internal_id;
        
        $newtype = BITSTREAM_TYPE_MISC;        
        switch ($filepath)
          {
          case 1:
            $newtype = BITSTREAM_TYPE_PAPER;
            break;
          case 2:
          case 4:
            $newtype = BITSTREAM_TYPE_SOURCECODE;
            break;
          case 3:
            $newtype = BITSTREAM_TYPE_PAPER;
            break;
          default:
            break;
          }
        
        $revision = $this->getBitStreamRevision($bitquery_array['description']);
        $this->getLogger()->warn("---- Copying  " .$filepath);
        if(empty($revision) || !file_exists($filepath))
          {
          $this->getLogger()->warn("---- Copy failed. Revision empty of path doesn't exist.");
          continue;
          }
        
        foreach($revision as $r)
          {
          if(!isset($bitstreams[$r])) $bitstreams[$r] = array();
          $bitstreams[$r][] = array('filename' => $filename,
              'path' => $filepath,
              'type' => $newtype);
          }
        }

      // Get Logo
      $logoPath = UtilityComponent::getTempDirectory()."/logo.jpg";
      unlink($logoPath);
      $logoFound = false;
      copy("http://code.osehra.org/journal/download/logopublication/".$item_id."/big", $logoPath);
      $c = file_get_contents($logoPath);   
      if(!empty($c))
        {
        $logoFound = true;
        }
        
      $sqlRevision = pg_query("SELECT * FROM isj_revision WHERE publication ='".$publicationId."' ORDER BY revision ASC ");
      while($ij_revisionArray = pg_fetch_array($sqlRevision))
        {
        $itemRevisionDao = new ItemRevisionDao();
        $itemRevisionDao->setChanges($ij_revisionArray['comments']);
        $itemRevisionDao->setUser_id($authorid);
        $itemRevisionDao->setDate(date('c', strtotime($ij_revisionArray['date'])));
        $itemRevisionDao->setLicenseId(null);
        MidasLoader::loadModel("Item")->addRevision($resourceDao, $itemRevisionDao);
        
        $resourceDao->setRevision($itemRevisionDao);        
        
        $resourceDao->setSubmitter($author);
        $resourceDao->setInstitution($institution);     
        $resourceDao->setAuthors($publicationAuthorList);     
        $resourceDao->setCategories($publicationCat);     
        $resourceDao->setCopyright("");     
        $resourceDao->setDisclaimer("");     
        $resourceDao->setTags($tags);     
        $resourceDao->setRelated("");     
        $resourceDao->setGrant($grant);     
        $resourceDao->setHandle($handle);     
        
        $resourceDao->enable();
        
        $resourceDao->setMetaDataByQualifier("old_id", $publicationId);
        
        $revisionNumber = $itemRevisionDao->getRevision();
        
        if($logoFound)
          {
          if(!isset($bitstreams[$revisionNumber])) $bitstreams[$revisionNumber] = array();
          $bitstreams[$revisionNumber][] = array('filename' =>  "logo.jpg",
              'path' => $logoPath,
              'type' => BITSTREAM_TYPE_THUMBNAIL);
          }
        
        if(!empty($bitstreams[$revisionNumber]))
          {
          foreach($bitstreams[$revisionNumber] as $bitstream)
            {
            $this->getLogger()->warn("---- Adding  Path:" .$bitstream['path']);
            $this->getLogger()->warn("---- Adding  MD5 " .UtilityComponent::md5file($bitstream['path']));
            // Add bitstreams to the revision
            $bitstreamDao = new BitstreamDao;
            $bitstreamDao->setName($bitstream['filename']);
            // Upload the bitstream
            $assetstoreDao = $Assetstore->load($this->assetstoreId);
            $bitstreamDao->setPath($bitstream['path']);
            $bitstreamDao->fillPropertiesFromPath();            
            $bitstreamDao->setAssetstoreId($this->assetstoreId);
            $this->getLogger()->warn("---- Adding  Checksum " .$bitstreamDao->getChecksum());

            $UploadComponent = new UploadComponent();
            $UploadComponent->uploadBitstream($bitstreamDao, $assetstoreDao, true);

            // Upload the bitstream ifnecessary (based on the assetstore type)
            $ItemRevision->addBitstream($itemRevisionDao, $bitstreamDao);
            
            MidasLoader::loadComponent("Bitstream", "journal")->setType($bitstreamDao, $bitstream['type']);   
            if($bitstream['type'] == BITSTREAM_TYPE_THUMBNAIL)
              {
              $resourceDao->setLogo($bitstream);
              }
            unset($UploadComponent);
            }
          }
        }
      }
    } // end _createFolderForItem()

  /** function to create the collections */
  private function _createFolderForCollection($communityId, $parentFolderid)
    {
    $this->getLogger()->warn("- Creating issues");
    $Folder = MidasLoader::loadModel("Folder");
    $User = MidasLoader::loadModel("User");
    $Folderpolicygroup = MidasLoader::loadModel("Folderpolicygroup");
    $Folderpolicyuser = MidasLoader::loadModel("Folderpolicyuser");
    $parentFolder = MidasLoader::loadModel("Folder")->load($parentFolderid);
    $communityDao = MidasLoader::loadModel("Folder")->getCommunity($parentFolder);
    
    $colquery = pg_query("SELECT collection_id FROM community2collection   WHERE community_id='$communityId' ORDER BY collection_id");
    while($collist_array = pg_fetch_array($colquery))
      {
      $collection_id = $collist_array['collection_id'];
      $sql = pg_query("SELECT collection_id, name, short_description, license, introductory_text FROM collection WHERE collection_id=".$collection_id);
      $colquery_array = pg_fetch_assoc($sql);
      if(empty($colquery_array)) continue;
      $name = $colquery_array['name'];
      $short_description = $colquery_array['short_description'];
      $introductory_text = $colquery_array['introductory_text'];
      $license = $colquery_array['license'];
      $issueDao = false;
      try
        {
        // Add IJ information
        $sql = pg_query("SELECT * FROM isj_journal WHERE collectionid=".$collection_id);
        $ij_journalArray = pg_fetch_assoc($sql);
        
        // Create the folder for the community        
        $community  = MidasLoader::loadModel("Community")->load($communityId);
        $issueDao = MidasLoader::newDao('IssueDao', 'journal');
        
        $issueDao->setParentId($parentFolderid);
        $issueDao->setName($name);
        $this->getLogger()->warn("-- Creating ".$name);
        MidasLoader::loadModel("Folder")->save($issueDao);
        $issueDao->InitValues();
        
        $anonymousGroup = MidasLoader::loadModel("Group")->load(MIDAS_GROUP_ANONYMOUS_KEY);
        MidasLoader::loadModel("Folderpolicygroup")->createPolicy($anonymousGroup, $issueDao, MIDAS_POLICY_READ); 
        
        $editorGroup = MidasLoader::loadModel("Group")->createGroup($communityDao, "Issue_".$issueDao->getKey());
        MidasLoader::loadModel("Folderpolicygroup")->createPolicy($editorGroup, $issueDao, MIDAS_POLICY_ADMIN);
        

        if(isset($ij_journalArray['paperdue_date'])) 
          {
          $date = $ij_journalArray['paperdue_date'];
          $datearray = array('year' => substr($date, 6, 4), 'month' => substr($date, 0, 2), 'day' => substr($date, 3, 2));
          $date = new Zend_Date($datearray);
          $date = $date->toString("c");
          $issueDao->paperdue_date = $date;
          }
        if(isset($ij_journalArray['decision_date'])) 
          {
          $date = $ij_journalArray['decision_date'];
          $datearray = array('year' => substr($date, 6, 4), 'month' => substr($date, 0, 2), 'day' => substr($date, 3, 2));
          $date = new Zend_Date($datearray);
          $date = $date->toString("c");
          $issueDao->decision_date = $date;
          }
        if(isset($ij_journalArray['publication_date']))
          {
          $date = $ij_journalArray['publication_date'];
          $datearray = array('year' => substr($date, 6, 4), 'month' => substr($date, 0, 2), 'day' => substr($date, 3, 2));
          $date = new Zend_Date($datearray);
          $date = $date->toString("c");
          $issueDao->publication_date = $date;
          }
          
        $issueDao->short_description = $short_description;
        $issueDao->introductory_text = $introductory_text;
        $issueDao->readerLicense = $license;
        
        $issueDao->initialized = true;
        $issueDao->save();
        
        // Assign the policies to the folder as the same as the parent folder
        $folder = $Folder->load($parentFolderid);
        $policyGroup = $folder->getFolderpolicygroup();
        $policyUser = $folder->getFolderpolicyuser();
        foreach($policyGroup as $policy)
          {
          $group = $policy->getGroup();
          $policyValue = $policy->getPolicy();
          $Folderpolicygroup->createPolicy($group, $issueDao, $policyValue);
          }
        foreach($policyUser as $policy)
          {
          $user = $policy->getUser();
          $policyValue = $policy->getPolicy();
          $Folderpolicyuser->createPolicy($user, $issueDao, $policyValue);
          }

        // Add specific MIDAS policies for users (not dealing with groups)
        $policyquery = pg_query("SELECT max(action_id) AS actionid, eperson.eperson_id, eperson.email
                                FROM resourcepolicy
                                LEFT JOIN eperson ON (eperson.eperson_id=resourcepolicy.eperson_id)
                                 WHERE epersongroup_id IS NULL AND resource_type_id=".MIDAS2_RESOURCE_COLLECTION.
                                 " AND resource_id=".$collection_id." GROUP BY eperson.eperson_id, email");

        while($policyquery_array = pg_fetch_array($policyquery))
          {
          $actionid = $policyquery_array['actionid'];
          $email = $policyquery_array['email'];
          if($actionid > 1)
            {
            $policyValue = MIDAS_POLICY_ADMIN;
            }
          else if($actionid == 1)
            {
            $policyValue = MIDAS_POLICY_WRITE;
            }
          else
            {
            $policyValue = MIDAS_POLICY_READ;
            }
          $userDao = $User->getByEmail($email);
          $Folderpolicyuser->createPolicy($userDao, $issueDao, $policyValue);
          }         
        }
      catch(Zend_Exception $e)
        {
        $this->getLogger()->info($e->getMessage());
        //Zend_Debug::dump($e);
        //we continue
        }

      if($issueDao)
        {
        $this->getLogger()->warn("--- Creating Items ");
        // We should create the item
        $this->_createFolderForItem($collection_id, $issueDao->getFolderId());
        }
      else
        {
        echo "Cannot create Folder for collection: ".$name."<br>";
        }
      }
    } // end _createFolderForCollection()


  /** */
  function migrate($userid)
    {
    $this->userId = $userid;

    $this->getLogger()->warn("Starting migration");
    // Connect to the local PGSQL database
    ob_start();  // disable warnings
    $pgdb = pg_connect("host='".$this->midas2Host."' port='".$this->midas2Port."' dbname='".$this->midas2Database.
                       "' user='".$this->midas2User."' password='".$this->midas2Password."'");
    ob_end_clean();
    $this->getLogger()->warn("Connected to the PG Database");
    if($pgdb === false)
      {
      throw new Zend_Exception("Cannot connect to the MIDAS2 database.");
      }

    // Check that the password prefix is not defined
    if(Zend_Registry::get('configGlobal')->password->prefix != '')
      {
      throw new Zend_Exception("Password prefix cannot be set because MIDAS2 doesn't use salt.");
      }
     
    // STEP 1: Import the users
    $this->getLogger()->warn("Importing users");
    $User = MidasLoader::loadModel("User");
    $Group = MidasLoader::loadModel("Group");
    $query = pg_query("SELECT eperson_id, email, password, firstname, lastname FROM eperson");
    while($query_array = pg_fetch_array($query))
      {
      $email = $query_array['email'];
      $password = $query_array['password'];
      $firstname = $query_array['firstname'];
      $lastname = $query_array['lastname'];
      $eperson_id = $query_array['eperson_id'];
      try
        {
        $userDao = $User->createUser($email, false, $firstname, $lastname, 0, $password);           
        $User->save($userDao);
        $this->epersonToUser[$eperson_id] = $userDao->getKey();
        $this->getLogger()->warn("- ".$email." created");
        }
      catch(Zend_Exception $e)
        {
        $this->getLogger()->info($e->getMessage());
        //Zend_Debug::dump($e);
        //we continue
        }
      }
    
    $this->getLogger()->warn("Adding institutions");
    $query = pg_query("SELECT erperson_id, id, institution FROM isj_user");
    while($query_array = pg_fetch_array($query))
      {
      $id = $query_array['id'];
      $institution = $query_array['institution'];
      $eperson_id = $query_array['erperson_id'];
      try
        {
        if(!isset($this->epersonToUser[$eperson_id]))continue;
        $userDao = MidasLoader::loadModel("User")->load($this->epersonToUser[$eperson_id]);
        $userDao->setCompany($institution);
        $this->getLogger()->warn("- Adding ".$institution." to ".$userDao->getEmail());
        MidasLoader::loadModel("User")->save($userDao);
        $this->ijuserToUser[$id] = $userDao->getKey();
        }
      catch(Zend_Exception $e)
        {
        $this->getLogger()->info($e->getMessage());
        //Zend_Debug::dump($e);
        //we continue
        }
      }
      
    // STEP 2: Import Categories & tookits
    $this->getLogger()->warn("Adding categories");
    $categoryDao = MidasLoader::newDao('CategoryDao', 'journal');
    $categoryDao->setName("Category");
    $categoryDao->setParentId(-1);
    MidasLoader::loadModel("Category", "journal")->save($categoryDao);

    
    $toolkitDao = MidasLoader::newDao('CategoryDao', 'journal');
    $toolkitDao->setName("Packages");
    $toolkitDao->setParentId(-1);
    MidasLoader::loadModel("Category", "journal")->save($toolkitDao);
      
    $query = pg_query("SELECT * from isj_category");
    while($query_array = pg_fetch_array($query))
      {
      $id = $query_array['id'];
      $name = $query_array['description'];
      $c = MidasLoader::newDao('CategoryDao', 'journal');
      $c->setName($name);
      $c->setParentId($categoryDao->getKey());
      MidasLoader::loadModel("Category", "journal")->save($c);
      
      $this->categoriesReference[$id] = $c->getKey();
      }

    $query = pg_query("SELECT * from isj_toolkit");
    while($query_array = pg_fetch_array($query))
      {
      $id = $query_array['id'];
      $name = $query_array['name'];
      $c = MidasLoader::newDao('CategoryDao', 'journal');
      $c->setName($name);
      $c->setParentId($toolkitDao->getKey());
      MidasLoader::loadModel("Category", "journal")->save($c);
      
      $this->tookitReference[$id] = $c->getKey();
      }
      
    // STEP 3: Import the communities. The MIDAS2 TopLevel communities are communities in MIDAS3
    $this->getLogger()->warn("Adding communities");
    $Community = MidasLoader::loadModel("Community");
    $query = pg_query("SELECT community_id, name, short_description, introductory_text FROM community");
    while($query_array = pg_fetch_array($query))
      {
      $community_id = $query_array['community_id'];
      $name = $query_array['name'];
      $short_description = $query_array['short_description'];
      $introductory_text = $query_array['introductory_text'];
      $communityDao = false;
      try
        {
        // Check the policies for the community
        // If anonymous can access then we set it public
        $policyquery = pg_query("SELECT policy_id FROM resourcepolicy WHERE resource_type_id=".MIDAS2_RESOURCE_COMMUNITY.
                                " AND resource_id=".$community_id." AND epersongroup_id=0");
        $privacy = MIDAS_COMMUNITY_PRIVATE;
        if(pg_num_rows($policyquery) > 0)
          {
          $privacy = MIDAS_COMMUNITY_PUBLIC;
          }
        $this->getLogger()->warn("- Adding ".$name);
        $communityDao = $Community->createCommunity($name, $short_description, $privacy, NULL); // no user

        
        if(!$communityDao)
          {
          $this->getLogger()->warn("Unable to create community. It will fail.");
          }
        $this->getLogger()->warn("- Added ".$communityDao->getName());

        // Add the users to the community
        // MIDAS2 was not using the group heavily so we ignore them. This would have to be a manual step
        $policyquery = pg_query("SELECT max(action_id) AS actionid, eperson.eperson_id, eperson.email
                                FROM resourcepolicy
                                LEFT JOIN eperson ON (eperson.eperson_id=resourcepolicy.eperson_id)
                                 WHERE epersongroup_id IS NULL AND resource_type_id=".MIDAS2_RESOURCE_COMMUNITY.
                                 " AND resource_id=".$community_id." GROUP BY eperson.eperson_id, email");

        while($policyquery_array = pg_fetch_array($policyquery))
          {
          $actionid = $policyquery_array['actionid'];
          $email = $policyquery_array['email'];
          if($actionid > 1)
            {
            $memberGroupDao = $communityDao->getAdminGroup();
            }
          else if($actionid == 1)
            {
            $memberGroupDao = $communityDao->getModeratorGroup();
            }
          else
            {
            $memberGroupDao = $communityDao->getMemberGroup();
            }
          $userDao = $User->getByEmail($email);
          $Group->addUser($memberGroupDao, $userDao);
          }
        }
      catch(Zend_Exception $e)
        {
        $this->getLogger()->info($e->getMessage());
        //Zend_Debug::dump($e);
        //we continue
        }

      if(!$communityDao)
        {
        $communityDao = $Community->getByName($name);
        }

      if($communityDao)
        {
        $folderId = $communityDao->getFolderId();
        $this->_createFolderForCollection($community_id, $folderId);
        }
      else
        {
        echo "Cannot create community: ".$name."<br>";
        }
      } // end while loop

    // STEP 4:Comments
    $this->getLogger()->warn("Adding comment");
    $query = pg_query("SELECT * FROM isj_revision_comment");
    while($query_array = pg_fetch_array($query))
      {
      $item = $this->getItemByAllId($query_array['publication']);
      $date = date('c', strtotime($query_array['date']));
      $epersonId = $query_array['eperson_id'];
      $userDao = MidasLoader::loadModel("User")->load($this->epersonToUser[$epersonId]);
      
      if($item && $userDao)
        {
        MidasLoader::loadModel('Itemcomment', 'comments')->addComment($userDao, $item, $query_array['comment']);
        }
      }    

    // Close the database connection
    pg_close($pgdb);
    } // end function migrate()
    
  public function getItemByAllId($id)
    {
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", "old_id");
    if(!$metadataDao)return false;
    $db = Zend_Registry::get('dbAdapter');
    
    $db = Zend_Registry::get('dbAdapter');
    $row = $db->fetchRow($db->select()
              ->from("metadatavalue", array("itemrevision_id"))
              ->where("value = ?",  $id)
              ->where("metadata_id = ?",  $metadataDao->getKey())
          );

    if(!empty($row) && isset($row['itemrevision_id']))
      {
      $revision = MidasLoader::loadModel("ItemRevision")->load($row['itemrevision_id']);
      if(!$revision)return false;
      return $revision->getItem();    
      }    
    return false;
    }
    
  private function getBitStreamRevision($description)
    {
    $description=substr($description,11);
    $description=substr($description,0,-1);
    $result=array();
    $revision=array();

    while(!empty($description))
    {
    ereg( "[0-9]*", $description, $revision);
    $description=substr($description,strlen($revision[0])+1);
    if(!empty($revision[0]))
      {
      $result[]=$revision[0];
      }
    }
    return @$result;
    }

} // end class
