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

class Journal_NotificationComponent extends AppComponent
{
  /**
   * This function is being called when a non-administrator
   * submit a journal article.
   * @TODO send out an email to all administrators who can
   * approve this submission.
   * @TODO send out an email to nofity author(submitter) that
   * the new submission is currently under review.
   */
  protected $defaultAdminEmail = "-fadmin@osehra.org";
  private $_layout;
  private $_view;

  public function sendForApproval($resourceDao, $userDao)
    {
    //TODO & make sure multiple notification
    // Need to send email notification to
    // *. Administrator of the community
    // *. Editors in this specific issue
    // *. Submitter
    $this->getLogger()->info("Send for approval is called: " . $resourceDao->getName());
    $fc = Zend_Controller_Front::getInstance();
    $baseUrl = UtilityComponent::getServerURL().$fc->getBaseUrl();
    $scriptpath = BASE_PATH . '/privateModules/journal/views/email';
    $this->_createEmailView($scriptpath, $baseUrl);
    $adminList = $this->_getSubmissionAdminEmails($resourceDao);
    $this->getLogger()->debug("AdminList is " . $adminList);
    // extract the editor group based resourceDao
    $editList = $this->_getSubmissionEditorEmails($resourceDao);
    $this->getLogger()->debug("editList is " . $editList);
    $name = $resourceDao->getName();
    $description = $resourceDao->getDescription();
    $sourceLicense = $resourceDao->getSourceLicense();
    $attributionPolicy = $resourceDao->getAgreedAttributionPolicy();
    $handle = $resourceDao->getHandle();
    $authors = $resourceDao->getAuthors();
    $itemId = $resourceDao->getItemId();
    $revisionId = $resourceDao->getRevision()->itemrevision_id;
    $authList = '';

    if(!empty($sourceLicense))
      {
      switch (intval($sourceLicense))
        {
        case 1:
          $license = "Apache 2";
          break;
        case 2:
          $license = "Public Domain";
          break;
        case 3:
          $license = "Other";
          break;
        }
      }
    else
      {
      $license = "No License Specified";
      }

    if ($attributionPolicy == 1)
      {
      $attributionPolicy = "Yes";
      }
    else
      {
      $attributionPolicy = "No";
      }

    foreach ($authors as $author)
      {
      $authList .= join(" ", $author) . ",";
      }
    if (!empty($authList)) $authList = substr($authList, 0, -1);
    $approveLink = "/journal/submit?revisionId=" . $revisionId;
    $baseUrl = UtilityComponent::getServerURL().$fc->getBaseUrl();
    $surveyLink = $baseUrl."/journal/survey?id=".$handle;
    Zend_Registry::get('notifier')->notifyEvent('EVENT_JOURNAL_SUBMIT_APPROVAL', array('revision_id' => $revisionId, 'handle' => $handle));

    $this->_view->assign("name", $name);
    $this->_view->assign("author", $authList);
    $this->_view->assign("description", $description);
    $this->_view->assign("license", $license);
    $this->_view->assign("attributionPolicy", $attributionPolicy);
    $this->_view->assign("link", $approveLink);
    $this->_view->assign("surveyLink", $surveyLink);
    $this->_layout->assign("content", $this->_view->render('sendforapproval.phtml'));
    $bodyText = $this->_layout->render('layout.phtml');


    $this->getLogger()->debug("Body Text is " . $bodyText);
    $subject = 'New Submission - Pending Approval: ' . $name;
    $to = '';
    $emailLstArray = array($editList, $adminList);
    $bccList = $this->_formBccList($emailLstArray);
    $headers = $this->_formMailHeader("", "", $bccList);
    $this->getLogger()->info("Email Header is " . $headers);
    // send mail to the editors/admins for approval
    $result = mail($to, $subject, $bodyText, $headers, $this->defaultAdminEmail);
    $this->getLogger()->debug("mail result is " . $result);
    // send mail to the submitter
    $this->_createEmailView($scriptpath, $baseUrl);
    $readlink = "/journal/view/" . $revisionId;
    $name = $userDao->getFullName();
    $this->_view->assign("name", $name);
    $this->_view->assign("link", $readlink);
    $this->_layout->assign("content", $this->_view->render('waitforapproval.phtml'));
    $contactEmail = $userDao->getEmail();
    $headers = $this->_formMailHeader($contactEmail, null, null);
    $bodyText = $this->_layout->render('layout.phtml');
    $this->getLogger()->debug("Body Text is " . $bodyText);
    $this->getLogger()->info("Email Header is " . $headers);
    $result = mail($to, $subject, $bodyText, $headers, $this->defaultAdminEmail);
    $this->getLogger()->debug("mail result is " . $result);
    }

  /**
   * This function is being called when a new journal article is submitted.
   * @TODO send out email to notify author as well as all users that are
   * subscribe to this notification.
   */
  public function newArticle($resourceDao)
    {
    $this->getLogger()->info("New Article is Added");
    // @TODO Check user settings, but I do not think it has been implemented yet
    $fc = Zend_Controller_Front::getInstance();
    $baseUrl = UtilityComponent::getServerURL().$fc->getBaseUrl();
    $scriptpath = BASE_PATH . '/privateModules/journal/views/email';
    $this->_createEmailView($scriptpath, $baseUrl);
    $submitter = $resourceDao->getSubmitter();
    $contactEmail = '';
    if ($submitter)
      {
      $contactEmail = $submitter->getEmail();
      }
    $this->getLogger()->debug("Contact Email is " . $contactEmail);
    $name = $resourceDao->getName();
    $description = $resourceDao->getDescription();
    $handle = $resourceDao->getHandle();
    $authors = $resourceDao->getAuthors();
    $itemId = $resourceDao->getItemId();
    $revisionId = $resourceDao->getRevision()->itemrevision_id;
    $authList = '';
    foreach ($authors as $author)
      {
      $authList .= join(" ", $author) . ",";
      }
    if (!empty($authList)) $authList = substr($authList, 0, -1);
    $handleLink = "http://hdl.handle.net/" . $handle;
    $this->_view->assign("name", $name);
    $this->_view->assign("author", $authList);
    $this->_view->assign("description", $description);
    $this->_view->assign("link", $handleLink);
    $this->_layout->assign("content", $this->_view->render('newsubmission.phtml'));
    $bodyText = $this->_layout->render('layout.phtml');
    $this->getLogger()->debug("Body Text is " . $bodyText);
    $subject = 'New Submission: ' . $name;
    $to = '';
    $subList = $this->_getNewSubmissionSubscribeList();
    $editList = $this->_getSubmissionEditorEmails($resourceDao);
    $adminList = $this->_getSubmissionAdminEmails($resourceDao);
    $emailLstArray = array($subList, $editList, $adminList);
    $bccList = $this->_formBccList($emailLstArray);
    if (!empty($contactEmail))
      {
        $bccList .= ',' . $contactEmail; # append contact email to the last.
      }
    $headers = $this->_formMailHeader('', null, $bccList);
    $this->getLogger()->info("Email Header is " . $headers);
    // send mail to the editors
    mail($to, $subject, $bodyText, $headers, $this->defaultAdminEmail);
    }

  /**
   * This function is being called when a journal article is edited or a
   * new revision is created.
   * @TODO send out email to notify author as well as all users that are
   * subscribe to this notification.
   */
  public function updatedArticle($resourceDao)
    {
    $this->getLogger()->info("Article is Updated");
    // @TODO Check user settings, but I do not think it has been implemented yet
    $fc = Zend_Controller_Front::getInstance();
    $baseUrl = UtilityComponent::getServerURL().$fc->getBaseUrl();
    $scriptpath = BASE_PATH . '/privateModules/journal/views/email';
    $this->_createEmailView($scriptpath, $baseUrl);
    $submitter = $resourceDao->getSubmitter();
    $contactEmail = '';
    if ($submitter)
      {
      $contactEmail = $submitter->getEmail();
      }
    $this->getLogger()->debug("Contact Email is " . $contactEmail);
    $name = $resourceDao->getName();
    $description = $resourceDao->getDescription();
    $handle = $resourceDao->getHandle();
    $authors = $resourceDao->getAuthors();
    $itemId = $resourceDao->getItemId();
    $revision_notes = $resourceDao->getRevisionNotes();
    $revisionId = $resourceDao->getRevision()->itemrevision_id;
    $authList = '';
    foreach ($authors as $author)
      {
      $authList .= join(" ", $author) . ",";
      }
    if (!empty($authList)) $authList = substr($authList, 0, -1);
    $handleLink = "http://hdl.handle.net/" . $handle;
    $this->_view->assign("name", $name);
    $this->_view->assign("author", $authList);
    $this->_view->assign("description", $description);
    $this->_view->assign("link", $handleLink);
    $this->_view->assign("revision_notes", $revision_notes);
    $this->_layout->assign("content", $this->_view->render('updatedsubmission.phtml'));
    $bodyText = $this->_layout->render('layout.phtml');
    $this->getLogger()->debug("Body Text is " . $bodyText);
    $subject = 'Updated Submission: ' . $name;
    $to = '';
    $subList = $this->_getNewSubmissionSubscribeList();
    $editList = $this->_getSubmissionEditorEmails($resourceDao);
    $adminList = $this->_getSubmissionAdminEmails($resourceDao);
    $emailLstArray = array($subList, $editList, $adminList);
    $bccList = $this->_formBccList($emailLstArray);
    if (!empty($contactEmail))
      {
        $bccList .= ',' . $contactEmail; # append contact email to the last.
      }
    $headers = $this->_formMailHeader('', null, $bccList);
    $this->getLogger()->info("Email Header is " . $headers);
    // send mail to the editors
    mail($to, $subject, $bodyText, $headers, $this->defaultAdminEmail);
    }

  /**
   * This function is being called whenever a new comments is added to a
   * journal.
   * @TODO send out email to notify author as well as all users that are
   * subscribe to this notification.
   */
  public function newComment($commentDao)
    {
    $this->getLogger()->info("New Comment is Added");
    $itemId = $commentDao->getItemId();
    $userId = $commentDao->getUserId();
    $comment = $commentDao->getComment();
    $item = MidasLoader::loadModel("Item")->load($itemId);
    $userDao = MidasLoader::loadModel("User")->load($userId);
    $fc = Zend_Controller_Front::getInstance();
    $baseUrl = UtilityComponent::getServerURL().$fc->getBaseUrl();
    $scriptpath = BASE_PATH . '/privateModules/journal/views/email';
    $this->_createEmailView($scriptpath, $baseUrl);
    // No need to check the permission here as it should have some access
    // to this journal item to make the comment
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
    $submitter = $resourceDao->getSubmitter();
    $contactEmail = '';
    if ($submitter)
      {
      $contactEmail = $submitter->getEmail();
      }
    $title = $resourceDao->getName();
    $handle = $resourceDao->getHandle();
    $this->_view->assign("name", $userDao->getFullName());
    $this->_view->assign("title", $title);
    $this->_view->assign("comments", $comment);
    $handleLink = "http://hdl.handle.net/" . $handle;
    $this->_view->assign("link", $handleLink);
    $this->_layout->assign("content", $this->_view->render('newcomment.phtml'));
    $bodyText = $this->_layout->render('layout.phtml');
    $this->getLogger()->debug("Body Text is " . $bodyText);
    $subject = 'Comment Added - Submission: ' . $title;
    $to = '';
    // form the email headers part
    //$editList = $this->_getSubmissionEditorEmails($resourceDao);
    $adminList = $this->_getSubmissionAdminEmails($resourceDao);

    //Only sends the comment email to the general public if the submission is marked as public
    // Method for determining private status taken from controllers/ViewController.php line 205
    $isPrivate = true;
    foreach($resourceDao->getItempolicygroup() as $policy)
      {
      if($policy->getGroupId() == MIDAS_GROUP_ANONYMOUS_KEY)
        {
        $isPrivate = false;
        }
      }
    // end determining section

    if (!$isPrivate)
      {
      $commentList = $this->_getCommentSubscribeList($resourceDao);
      }
    $emailLstArray = array($commentList,$adminList);
    $bccList = $this->_formBccList($emailLstArray);
    if (!empty($contactEmail))
      {
        $bccList .= ',' . $contactEmail; # append contact email to the last.
      }
    $headers = $this->_formMailHeader('', null, $bccList);
    $this->getLogger()->info("Email Header is " . $headers);
    // send mail to the editors
    mail($to, $subject, $bodyText, $headers, $this->defaultAdminEmail);
    }
  /**
   * This function is being called whenever a new review is added to a
   * journal.
   * @TODO send out email to notify author as well as all users that are
   * subscribe to this notification.
   */
  public function newReview($reviewDao)
    {
    $this->getLogger()->info("New Review is Added");
    $fc = Zend_Controller_Front::getInstance();
    $baseUrl = UtilityComponent::getServerURL().$fc->getBaseUrl();
    $scriptpath = BASE_PATH . '/privateModules/journal/views/email';
    $this->_createEmailView($scriptpath, $baseUrl);
    $userId = $reviewDao->getUserId();
    $revision_id = $reviewDao->getRevisionId();
    $reviewId = $reviewDao->getKey();
    $userDao = MidasLoader::loadModel("User")->load($userId);
    $name = $userDao->getFullName();
    $revision = MidasLoader::loadModel("ItemRevision")->load($revision_id);
    $itemDao = $revision->getItem();
    $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $itemDao->toArray(), "journal");
    $submitter = $resourceDao->getSubmitter();
    $contactEmail = '';
    if ($submitter)
      {
      $contactEmail = $submitter->getEmail();
      }
    $title = $resourceDao->getName();
    $viewLink =  $baseUrl . "/reviewosehra/submit?review_id=" . $reviewId;
    $this->_view->assign("name", $name);
    $this->_view->assign("title", $title);
    $this->_view->assign("link", $viewLink);
    $this->_layout->assign("content", $this->_view->render('newreview.phtml'));
    $bodyText = $this->_layout->render('layout.phtml');
    $this->getLogger()->debug("Body Text is " . $bodyText);
    $subject = 'New Review: ' . $title;
    $to = '';
    // form the email headers part
    $subList = $this->_getNewReviewSubscribeList();
    // form the email headers part
    $editList = $this->_getSubmissionEditorEmails($resourceDao);
    $adminList = $this->_getSubmissionAdminEmails($resourceDao);
    $emailLstArray = array($subList, $editList, $adminList);
    $bccList = $this->_formBccList($emailLstArray);
    if (!empty($contactEmail))
      {
        $bccList .= ',' . $contactEmail; # append contact email to the last.
      }
    $headers = $this->_formMailHeader('', null, $bccList);
    $this->getLogger()->info("Email Header is " . $headers);
    // send mail to the submitter, editor as well as admins
    mail($to, $subject, $bodyText, $headers, $this->defaultAdminEmail);
    }

  /**
   * Get user status
   * @param UserDao $user
   * @return array
   */
  public function getUserNotificationStatus($user)
    {
    if(!$user instanceof UserDao) return array();
    $db = Zend_Registry::get('dbAdapter');
    $results = $db->query("SELECT journalmodule_notification_submission as submission, 
      journalmodule_notification_review as review, journalmodule_notification_comments as comment
      FROM user WHERE user_id='".$user->getKey()."'")->fetchAll();
    
    if(empty($results))return array();
    return array($results[0]['submission'], $results[0]['review'],$results[0]['comment']);
    }
    

  public function setUserNotificationStatus($user, $submission, $review,$comments)
    {
    if(!$user instanceof UserDao || !is_numeric($submission) || !is_numeric($review) || !is_numeric($comments)) return;
    $db = Zend_Registry::get('dbAdapter');
    $sql = "UPDATE `user` set journalmodule_notification_comments = ".$db->quote($comments).",journalmodule_notification_submission = ".$db->quote($submission).",
      journalmodule_notification_review = ".$db->quote($review)."where user_id=".$user->getKey();
    $db->query($sql);   
    }
    
  /**
   * Get all the user who wants to receive notifications
   * @return array
   */
  public function findWithSubmissionNotification()
    {
    $db = Zend_Registry::get('dbAdapter');
    $results = $db->query("SELECT user_id FROM user
      WHERE journalmodule_notification_submission='1'")
               ->fetchAll();
    
    $return = array();
    
    foreach($results as $result)
      {
      $return[] = MidasLoader::loadModel("User")->load($result['user_id']);
      }
     
    return $return;
    }
  /**
   * Get all the user who wants to receive notifications
   * @return array
   */
  public function findWithReviewNotification()
    {
    $db = Zend_Registry::get('dbAdapter');
    $results = $db->query("SELECT user_id FROM user
      WHERE journalmodule_notification_review='1'")
               ->fetchAll();
    
    $return = array();
    
    foreach($results as $result)
      {
      $return[] = MidasLoader::loadModel("User")->load($result['user_id']);
      }
     
    return $return;
    }

public function findWithCommentsNotification()
    {
    $db = Zend_Registry::get('dbAdapter');
    $results = $db->query("SELECT user_id FROM user
      WHERE journalmodule_notification_comments='1'")
               ->fetchAll();

    $return = array();

    foreach($results as $result)
      {
      $return[] = MidasLoader::loadModel("User")->load($result['user_id']);
      }

    return $return;
    }

  /**
   * private functions
   */
  private function _createEmailView($scriptpath, $baseUrl)
    {
    $this->_layout = new Zend_View();
    $this->_view = new Zend_View();
    $this->_layout->setScriptPath($scriptpath);
    $this->_view->setScriptPath($scriptpath);
    $this->_view->assign("webroot", $baseUrl);
    $this->_layout->assign("webroot", $baseUrl);
    }
  private function _formMailHeader($contactEmail, $ccList, $bccList)
    {
    $fromEmail = "OSEHRA Technical Journal <no-reply@osehra.org>";
    $replyEmail = "OSEHRA Technical Journal <no-reply@osehra.org>";
    $linesep = "\r\n";
    $headers = 'To: ' . $contactEmail . $linesep;
    $headers .= 'From: ' . $fromEmail . $linesep;
    $headers .= "Reply-To: " . $replyEmail . $linesep;
    if (!empty($ccList))
      {
      $headers .= "Cc: " . $ccList . $linesep;
      }
    if (!empty($bccList))
      {
      $headers .= "Bcc: " . $bccList . $linesep;
      }
    $headers .='X-Mailer: PHP/' . phpversion() . $linesep;
    $headers .= "MIME-Version: 1.0" . $linesep;
    $headers .= "Content-type: text/html; charset=iso-8859-1"; 
    return $headers;
    }
  private function _getSubmissionAdminEmails($resourceDao)
    {
    // extract the information from resourceDao
    $adminGroup = $resourceDao->getAdminGroup();
    $adminUsers = $adminGroup->getUsers();
    $adminList = '';
    foreach ($adminUsers as $adminUser)
      {
      $adminList .= $adminUser->getEmail() . ',';
      }
    if (!empty($adminList)) $adminList = substr($adminList, 0, -1);
    return $adminList;
    }
  private function _getSubmissionEditorEmails($resourceDao)
    {
    $folder = end($resourceDao->getFolders());
    $editGroup = '';
    $editList = '';
    $adminGroup = $resourceDao->getAdminGroup();
    foreach ($folder->getFolderpolicygroup() as $policy)
      {
      if ($policy->getPolicy() == MIDAS_POLICY_ADMIN && $adminGroup->getKey() != $policy->getGroupId())
        {
        $editGroup = $policy->getGroup();
        break;
        }
      }
    if (!empty($editGroup))
      {
      $editUsers = $editGroup->getUsers();
      foreach ($editUsers as $editUser)
        {
        $editList .= $editUser->getEmail() . ',';
        }
      if (!empty($editList)) $editList = substr($editList, 0, -1);
      }
    return $editList;
    }
  private function _getNewSubmissionSubscribeList()
    {
    $allSubscriberLst = '';
    $subscribers = $this->findWithSubmissionNotification();
    if (!empty($subscribers))
      {
        foreach ($subscribers as $subscriber)
          {
          $allSubscriberLst .= $subscriber->getEmail() . ',';
          }

        if (!empty($allSubscriberLst)) $allSubscriberLst = substr($allSubscriberLst, 0, -1);
      }
    return $allSubscriberLst;
    }

  private function _getNewReviewSubscribeList()
    {
    $allSubscriberLst = '';
    $subscribers = $this->findWithReviewNotification();
    if (!empty($subscribers))
      {
        foreach ($subscribers as $subscriber)
          {
          $allSubscriberLst .= $subscriber->getEmail() . ',';
          }

        if (!empty($allSubscriberLst)) $allSubscriberLst = substr($allSubscriberLst, 0, -1);
      }
    return $allSubscriberLst;
    }

  private function _getCommentSubscribeList()
    {
    $allSubscriberLst = '';
    $subscribers = $this->findWithCommentsNotification();
    if (!empty($subscribers))
      {
        foreach ($subscribers as $subscriber)
          {
          $allSubscriberLst .= $subscriber->getEmail() . ',';
          }

        if (!empty($allSubscriberLst)) $allSubscriberLst = substr($allSubscriberLst, 0, -1);
      }
    return $allSubscriberLst;
    }
  private function _formBccList($emailLstArray)
    {
    $bccList = '';
    foreach ($emailLstArray as $emailList)
      {
      if (!empty($bccList) && !empty($emailList))
        {
        $bccList .= "," . $emailList;
        }
      else
        {
        $bccList .= $emailList;
        }
      }
    return $bccList;
    }
  private function _testMail()
    {
    $to      = 'lij@osehra.org';
    $subject = 'the subject';
    $message = 'hello';
    $headers = 'From: no-reply@osehra.com' . "\r\n" .
        'Reply-To: no-reply@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $result = mail($to, $subject, $message, $headers);
    $this->getLogger()->debug("mail result is " . $result);
    }
} // end class
