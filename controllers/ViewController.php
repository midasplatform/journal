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

class Journal_ViewController extends Journal_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    if(isset($actionName) && is_numeric($actionName))
      {
      $this->_forward('index', null, null, array('revisionId' => $actionName));
      }
    parent::init();
    }

  /** List all the journals */
  function journalsAction()
    {
    $communities = MidasLoader::loadModel("Community")->getAll();
    foreach($communities as $key => $community)
      {
      if(!MidasLoader::loadModel("Community")->policyCheck($community, $this->userSession->Dao, MIDAS_POLICY_READ))
        {
        unset($communities[$key]);
        }
      }
    $this->view->communities = $communities;
    }

    /** List available downloads */
    function downloadAction()
      {
      $revisionId = $this->_getParam("revisionId");
      if(!isset($revisionId) || !is_numeric($revisionId))
        {
        throw new Zend_Exception("revisionId should be a number");
        }
      $revisionDao = MidasLoader::loadModel("ItemRevision")->load($revisionId);
      if($revisionDao === false)
        {
        throw new Zend_Exception("This item doesn't exist.", 404);
        }
      $itemDao = $revisionDao->getItem();
      if(!MidasLoader::loadModel("Item")->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_READ))
        {
        throw new Zend_Exception('Read permission required', 403);
        }

      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
      $resourceDao->setRevision($revisionDao);

      // Try to find paper bitstream
      $bitstreams = $resourceDao->getRevision()->getBitstreams();
      $paper = false;
      foreach($bitstreams as $b)
        {
        $type = MidasLoader::loadComponent("Bitstream", "journal")->getType($b);
        if($type == BITSTREAM_TYPE_PAPER)
          {
          $paper = $b;
          break;
          }
        }

      $this->view->resource = $resourceDao;
      $this->view->paper = $paper;
      }


  /** Show issue information (ajax) */
  function issueAction()
    {
    $this->disableLayout();
    $folderId = $this->_getParam('folderId');
    $folder = MidasLoader::loadModel("Folder")->load($folderId);
    $this->view->issue = MidasLoader::loadModel("Folder")->initDao("Issue", $folder->toArray(), "journal");
    }

  function biglogoAction()
    {
    $this->_forward("logo", "view", "journal", array('revisionId' => $_GET['revisionId'], 'size' => 300));
    }

  function logoAction()
    {
    $revisionId = $this->_getParam("revisionId");
    $size = $this->_getParam("size");

    if(!isset($revisionId) || !is_numeric($revisionId))
      {
      throw new Zend_Exception("revisionId should be a number");
      }
    $revisionDao = MidasLoader::loadModel("ItemRevision")->load($revisionId);
    if($revisionDao === false)
      {
      throw new Zend_Exception("This item doesn't exist.", 404);
      }
    $itemDao = $revisionDao->getItem();
    if(!MidasLoader::loadModel("Item")->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_READ))
      {
      throw new Zend_Exception('Read permission required', 403);
      }

    $this->disableLayout();
    $this->disableView();
    header('Content-Type: image/jpeg');
    header("Content-Disposition: attachment; filename=journal_".$revisionId.".jpg");

    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
    $resourceDao->setRevision($revisionDao);
    $logo = $resourceDao->getLogo();
    if(empty($logo))
      {
      $img_src_resource = imagecreatefromgif(__DIR__."/../public/images/journal.gif");
      list ($x, $y) = getimagesize(__DIR__."/../public/images/journal.gif");  //--- get size of img ---
      }
    else
      {
      $img_src_resource = null;
      $extension = strtolower(end(explode(".", $logo->getName())));
      switch ( $extension ) {
        case "jpg":
        case "peg":
          $img_src_resource = imagecreatefromjpeg($logo->getFullPath());
          break;
        case "gif":
          $img_src_resource = imagecreatefromgif($logo->getFullPath());
          break;
        case "png":
          $img_src_resource = imagecreatefrompng($logo->getFullPath());
          break;
        default:
          return;
        }
      list ($x, $y) = getimagesize($logo->getFullPath());  //--- get size of img ---
      }

    if(isset($size) && is_numeric($size)) $thumb = $size;
    else $thumb = 50;  //--- max. size of thumb ---
    if($x > $y)
      {
      $tx = $thumb;  //--- landscape ---
      $ty = round($thumb / $x * $y);
      }
    else
      {
      $tx = round($thumb / $y * $x);  //--- portrait ---
      $ty = $thumb;
      }

    $thb = imagecreatetruecolor($tx, $ty);  //--- create thumbnail ---
    imagecopyresampled($thb, $img_src_resource, 0, 0, 0, 0, $tx, $ty, $x, $y);
    ob_start(); // start a new output buffer
    imagejpeg( $thb, NULL, 100 );
    ob_end_clean; // stop this output buffer
    exit;
    }

  /** Display a resource*/
  function indexAction()
    {
    $revisionId = $this->_getParam("revisionId");
    if(!isset($revisionId) || !is_numeric($revisionId))
      {
      throw new Zend_Exception("revisionId should be a number");
      }
    $revisionDao = MidasLoader::loadModel("ItemRevision")->load($revisionId);
    if($revisionDao === false)
      {
      $this->disableView();
      echo "<br/><b>The requested article doesn't exist.</b>";
      return;
      }
    $itemDao = $revisionDao->getItem();
    if(!MidasLoader::loadModel("Item")->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_READ))
      {
      echo "<br/><b>This is a restricted content. This page cannot be shared or accessed directly.</b>";
      $this->disableView();
      return;
      }

    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
    $resourceDao->setRevision($revisionDao);
    $issue = end($resourceDao->getFolders());
    $community = MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot( $issue));
    $memberGroup = $community->getMemberGroup();

    // Check if public or private (If private, it means it requires approval
    $private = true;
    $isApproved = false;
    foreach($resourceDao->getItempolicygroup() as $policy)
      {
      if($policy->getGroupId() == $memberGroup->getKey())
        {
        $isApproved = true;
        }
      if($policy->getGroupId() == MIDAS_GROUP_ANONYMOUS_KEY)
        {
        $private = false;
        }
      }

    MidasLoader::loadModel("Item")->incrementViewCount($resourceDao);

    if(isset($_POST['exportType']))
      {
      $this->disableLayout();
      $this->disableView();
      MidasLoader::loadComponent("Export", "journal")->citation($resourceDao, $_POST['exportType']);
      return;
      }

    // Try to find paper bitstream
    $bitstreams = $resourceDao->getRevision()->getBitstreams();
    $paper = false;
    foreach($bitstreams as $b)
      {
      $type = MidasLoader::loadComponent("Bitstream", "journal")->getType($b);
      if($type == BITSTREAM_TYPE_PAPER)
        {
        $paper = $b;
        break;
        }
      }

    $oldWebsiteId = $resourceDao->getMetaDataByQualifier("old_id");
    $this->view->hasOldReview = false;
    $this->view->oldWebsiteUrl = false;
    if($oldWebsiteId)
      {
      $oldWebsiteUrl = MidasLoader::loadModel("Setting")->getValueByName('oldWebsiteUrl', "journal");
      if(!empty($oldWebsiteUrl))
        {
        $hasOldReview = $resourceDao->getMetaDataByQualifier("has_old_review");
        $revisionNumber = $resourceDao->getMetaDataByQualifier("old_revision");
        $this->view->oldWebsiteUrl = $oldWebsiteUrl."/browse/publication/".$oldWebsiteId->getValue()."/".$revisionNumber->getValue();
        $this->view->hasOldReview = $hasOldReview != false;
        }
      }

    // Send resource to the view
    $this->view->title .= ' - '.$resourceDao->getName();
    $this->view->metaDescription = substr($resourceDao->getDescription(), 0, 160);
    $tags = $resourceDao->getTags();
    $this->view->metaKeywords .= ", ".join(', ', $tags);
    $this->view->isPrivate = $private;
    $this->view->isApproved = $isApproved;
    $this->view->resource = $resourceDao;
    $this->view->issue =  $issue;
    $this->view->paper = $paper;
    $this->view->revisions =  $itemDao->getRevisions();;
    $this->view->community =  $community;
    $this->view->creationDate = MidasLoader::loadComponent("Date")->formatDate(strtotime($resourceDao->getDateCreation()));
    $this->view->termFrequency = file_get_contents("http://localhost:8983/solr/admin/luke?fl=text-journal.tags&wt=json&numTerms=200&reportDocCount=false");
    $this->view->isAuthor = MidasLoader::loadModel("Item")->policyCheck($resourceDao, $this->userSession->Dao, MIDAS_POLICY_WRITE);
    $this->view->isAdmin = $resourceDao->isAdmin($this->userSession->Dao);
    $this->view->baseHandle = MidasLoader::loadModel("Setting")->getValueByName('baseHandle', "journal");

    // Send to javascript
    $this->view->json['item'] = $itemDao->toArray();
    $this->view->json['item']['tags'] = $resourceDao->getTags();
    $this->view->json['item']['isAdmin'] = $this->view->isAdmin;
    $this->view->json['item']['isModerator'] = $this->view->isModerator;
    $this->view->json['modules'] = Zend_Registry::get('notifier')->callback('CALLBACK_CORE_ITEM_VIEW_JSON', array('item' => $itemDao));
    }

}//end class