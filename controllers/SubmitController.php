<?php

class Reviewosehra_SubmitController extends Reviewosehra_AppController
{
  // Initialization method. Called before every Action
  function init()
    {
    parent::init();
    }

  function indexAction()
    {
    $revision_id = $this->_getParam('revision_id');
    $review_id = $this->_getParam('review_id');
    $review = false;
    if(isset($revision_id) && !$this->logged)
      {
      $this->haveToBeLogged();
      return;
      }

    if(isset($review_id))
      {
      $review = MidasLoader::loadModel("Review", 'reviewosehra')->load($review_id);
      if($review)
        {
        $revision_id = $review->getRevisionId();
        }
      }

    if(!isset($revision_id) || !is_numeric($revision_id))
      {
      throw new Zend_Exception("revision_id should be a number");
      }
    $this->getLogger()->info("Current revision_id is " . $revision_id . " review_id is " . $review_id);
    $revision = MidasLoader::loadModel("ItemRevision")->load($revision_id);
    $itemDao = $revision->getItem();
    if($itemDao === false || !MidasLoader::loadModel("Item")->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_READ))
      {
      throw new Zend_Exception("This item doesn't exist or you don't have the permissions.");
      }
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
    $resourceDao->setRevision($revision);

    $reviewPhase = $resourceDao->getMetaDataByQualifier("reviewPhase");
    if($reviewPhase) $reviewPhase = $reviewPhase->getValue();
    else $reviewPhase = OSERHAREVIEW_LIST_PEERREVIEW;
    $this->getLogger()->info("Current review phase is " . $reviewPhase);
    if($this->_request->isPost())
      {
      $this->disableLayout();
      $this->disableView();

      if(!$this->logged)
        {
        throw new Zend_Exception("Please log in");
        return;
        }

      $content = $this->_getParam("content");
      $cacheSummary = $this->_getParam("cache_summary");
      $is_complete = $this->_getParam("is_complete");
      if($review)
        {
        if(!$resourceDao->isAdmin($this->userSession->Dao) && $this->userSession->Dao->getKey() != $review->getUserId())
          {
          throw new Zend_Exception("Permission error");
          return;
          }
        $reviewDao = $review;
        }
      else
        {
        $reviewDao = MidasLoader::newDao("ReviewDao", "reviewosehra");
        $reviewDao->setRevisionId($revision->getKey());
        $reviewDao->setUserId($this->userSession->Dao->getKey());
        $contentArray = JsonComponent::decode($content);
        $reviewDao->setType($contentArray['list']['type']);
        }
      $reviewDao->setContent($content);
      $reviewDao->setCacheSummary($cacheSummary);
      $reviewDao->setComplete($is_complete);
      MidasLoader::loadModel("Review", 'reviewosehra')->save($reviewDao);
      Zend_Registry::get('notifier')->callback('CALLBACK_REVIEW_ADDED', array('review' => $reviewDao));
      $resourceDao->setHasReviews();
      MidasLoader::loadModel("Item")->save($resourceDao);
      echo JsonComponent::encode($this->view->webroot."/journal/view/?revisionId=".$reviewDao->getRevisionId());
      return;
      }

    $isEditable = true;

    // look for existing review
    $questionslists = MidasLoader::loadModel("Questionlist", 'reviewosehra')->getAll();
    if($review)
      {
      if(!$this->logged || (!$resourceDao->isAdmin($this->userSession->Dao) && $this->userSession->Dao->getKey() != $review->getUserId()))
        {
        $isEditable = false;
        }
      $mainList = JsonComponent::decode($review->getContent());
      }
    else
      {
      $categories = $resourceDao->getCategories();
      $mainListTmp = false;
      foreach($questionslists as $list)
        {
        if($list->getCategoryId() == -1 && $list->getType() == $reviewPhase)
          {
          $mainListTmp = $list;
          break;
          }
        }
      foreach($categories as $cat)
        {
        foreach($questionslists as $list)
          {
          if($cat == $list->getCategoryId() && $list->getType() == $reviewPhase)   $mainListTmp = $list;
          }
        }

      if(!$mainListTmp)
        {
        throw new Zend_Exception("Unable to match a question list for this article. Please contact an administrator.");
        }
      $mainList = $mainListTmp->toArray();
      }

    $this->view->isEditable = $isEditable;
    $this->view->resource = $resourceDao;
    $this->view->listArray = $mainList;
    $this->view->json['listArray'] = $mainList;
    $this->view->json['listArray']['revision_id'] = $revision_id;
    $this->view->json['listArray']['review_id'] = $review_id;
    $this->view->lists = $questionslists;
    }

  /** upload a file (see jquery upload file)*/
  function uploadAction()
    {
    if(!$this->logged)
      {
      return;
      }

    $userDao = $this->userSession->Dao;
    $revision = MidasLoader::loadModel("ItemRevision")->load($_GET['revision_id']);

    if(!$revision)
      {
      return;
      }

    // Get PrivateFolder
    $item = $revision->getItem();
    $itemFolder = end($item->getFolders());
    $rootFolder = MidasLoader::loadModel("Folder")->getRoot($itemFolder);
    $communityFolders = $rootFolder->getFolders();
    $privateFolder = false;
    foreach($communityFolders as $f)
      {
      if($f->getName() == "Private")$privateFolder = $f;
      }

    if(!$privateFolder)
      {
      return;
      }

    $uploadRootFolder = $this->getTempDirectory()."/reviewosehrafiles";
    if(!file_exists($uploadRootFolder))
      {
      mkdir($uploadRootFolder);
      }
    $uploadFolder = $uploadRootFolder."/".time()."/";
    mkdir($uploadFolder);

    require_once __DIR__.'/components/UploadHandler.php';
    $upload_handler = new UploadHandler(array('upload_dir' => $uploadFolder,
                                              'max_number_of_files' => 1,
                                              'upload_url' => $this->view->webroot.'/journal/submit/upload'));


    header('Pragma: no-cache');
    header('Cache-Control: private, no-cache');
    header('Content-Disposition: inline; filename="files.json"');
    header('X-Content-Type-Options: nosniff');

    $returninfo = $upload_handler->post();

    if(isset($returninfo[0]) && $returninfo[0]->size > 0 && isset($upload_handler->filepath) && file_exists($upload_handler->filepath))
      {
      $userDao = $this->userSession->Dao;
      $userDao->setAdmin(1);
      $item = MidasLoader::loadComponent("Upload")->createUploadedItem($userDao, $upload_handler->filename,
                                $upload_handler->filepath, $privateFolder);
      $anonymousGroup = MidasLoader::loadModel('Group')->load(MIDAS_GROUP_ANONYMOUS_KEY);
      MidasLoader::loadModel('Itempolicygroup')->createPolicy($anonymousGroup, $item, MIDAS_POLICY_READ);
      $returninfo[0]->item_id = $item->getKey();
      unlink($uploadFolder);
      }

    echo json_encode($returninfo);
    exit;
    }
}//end class
