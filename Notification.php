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

require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';
class Journal_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'journal';
  public $_moduleComponents=array('Api');

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    $this->addTask('TASK_JOURNAL_SUBMIT_APPROVAL', 'runSurvey', "");
    $this->addEvent('EVENT_JOURNAL_SUBMIT_APPROVAL', 'TASK_JOURNAL_SUBMIT_APPROVAL');
    $this->addTask('TASK_JOURNAL_UPLOAD_GITHUB', 'processGithub', "");
    $this->addEvent('EVENT_JOURNAL_UPLOAD_GITHUB', 'TASK_JOURNAL_UPLOAD_GITHUB');
    $this->addTask('TASK_JOURNAL_UPDATESITEMAP', 'updateSitemap', "");
    $this->addEvent('EVENT_JOURNAL_UPDATESITEMAP', 'TASK_JOURNAL_UPDATESITEMAP');
    $this->addCallBack('CALLBACK_CORE_GET_CONFIG_TABS', 'getConfigTabs');
    $this->addCallBack('CALLBACK_CORE_AUTHENTICATION', 'authIntercept');
    $this->addCallBack('CALLBACK_COMMENTS_ADDED_COMMENT', 'commentAdded');
    $this->addCallBack('CALLBACK_REVIEW_ADDED', 'reviewAdded');
    }//end init

  /** Update sitemap.xml */
  public function updateSitemap($param)
    {
    MidasLoader::loadComponent("Sitemap", "journal")->generate();
    }


  /** Run OTJ Survey Script on Submission */
  public function runSurvey($param)
    {
    $revisionId = $param['revision_id'];
    // Execute the OTJ survey on the submission
    // First get the bitstream paths needed
    $db = Zend_Registry::get('dbAdapter');
    $journalDir = BASE_PATH.'/privateModules/journal/';
    $surveyDir = UtilityComponent::getTempDirectory();
    $results = $db->query("SELECT path from bitstream WHERE itemrevision_id='".$revisionId."'")->fetchAll();
    $commandArgs = '-d '.$surveyDir.' -z /usr/bin/7z';

    // Capture each path and only take the first 6 characters to capture the folder information
    foreach($results as $result)
      {
        $commandArgs .= " -p ".BASE_PATH."/data/assetstore/".substr($result['path'],0,5);
      }
    //Generate the commands to be executed to run the survey script and to move the file to
    // where the survey page will expect it to be

    $fullSurveyCmd = "/usr/bin/python ".$journalDir."otjSurvey.py ".$commandArgs;
    $surveyCommand = escapeshellcmd($fullSurveyCmd);
    exec($surveyCommand);
    // Move file to the OTJ_Survey directory
    $filelocation = BASE_PATH.'/privateModules/journal/OTJ_Survey/'.$revisionId."_Results.txt";
    $fullMvCmd = 'mv '.$surveyDir.'/SurveyResult.txt '.$filelocation;
    $mvCommand = escapeshellcmd($fullMvCmd);
    exec($mvCommand);
    }

  /** Backup github*/
  public function processGithub($param)
    {
    if(isset($param[0]['bitstream_id']))
      {
      $bitstream = MidasLoader::loadModel("Bitstream")->load($param[0]['bitstream_id']);
      if($bitstream)
        {
        $zipPath = "/tmp/journal/github_zip.zip";
        if(!file_exists("/tmp/journal"))
          {
          mkdir("/tmp/journal");
          }
        if(file_exists($zipPath)) unlink($zipPath);

        $name = str_replace(".zip", "", $bitstream->getName());
        $return = copy("https://github.com/".$name."/archive/master.zip", $zipPath);
        if($return && file_exists($zipPath))
          {
          $bitstream->setName($bitstream->getName().".zip");
          $bitstream->setPath($zipPath);
          $bitstream->setChecksum(md5_file($zipPath));
          $bitstream->fillPropertiesFromPath();
          $assetstoreDao = MidasLoader::loadModel('Assetstore')->getDefault();
          MidasLoader::loadComponent("Upload")->uploadBitstream($bitstream, $assetstoreDao, false);
          MidasLoader::loadModel('Bitstream')->save($bitstream);

          $revision = $bitstream->getItemrevision();
          $item = $revision->getItem();
          $item->setSizebytes(MidasLoader::loadModel('ItemRevision')->getSize($revision));
          MidasLoader::loadModel('Item')->save($item);
          }
        }
      }
    }

  /**
   * The goal is to convert old account to new ones
   */
  public function authIntercept($params)
    {
    $email = $params['email'];
    $password = $params['password'];

    $userDao = MidasLoader::loadModel("User")->getByEmail($email);
    if($userDao && $userDao->getSalt() == md5($password))
      {
      MidasLoader::loadModel("User")->convertLegacyPasswordHash($userDao, $password);
      }
    }

   /** get Config Tabs */
  public function getConfigTabs($params)
    {
    $user = $params['user'];
    $fc = Zend_Controller_Front::getInstance();
    $webroot = $fc->getBaseUrl();
    return array('Notifications' => $webroot.'/journal/user/notification?userId='.$user->getKey());
    }

  /**
   *  Notify comments are added via email
   */
  public function commentAdded($params)
    {
    $commentDao = $params['comment'];
    MidasLoader::loadComponent("Notification", "journal")->newComment($commentDao);
    }
  /**
   *  Notify reviews are added via email
   */
  public function reviewAdded($params)
    {
    $reviewDao = $params['review'];
    MidasLoader::loadComponent("Notification", "journal")->newReview($reviewDao);
    }
  } //end class
?>




