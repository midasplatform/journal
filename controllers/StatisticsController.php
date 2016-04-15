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

/** Articles statistics */
class Journal_StatisticsController extends Journal_AppController
{

  function indexAction()
    {
    $date = isset($_GET["date"]) ? $_GET["date"]:date('Y');

    $date_next = $date + 1;
    $date_previous = $date - 1;

    $byMonth = array();
    $publications = array();
    $quarterStats = array();
    for($i = 1; $i < 13; $i++)
      {
      $tmpData = $date."-".(($i < 10)? "0".$i:$i );
      $byMonth[$i] = $this->getPublications($tmpData);
      $publications = array_merge($publications, $byMonth[$i][3]);
      $byMonth[$i][3] = $this->getDownloads($tmpData);
      }
    for($i = 1; $i < 13; $i=$i+3)
      {
      $tmpData = (($i < 10)? "0".$i:$i );
      $quarterStats[$i]= $this->getTopDownloads($date, $tmpData);
      }

    ksort($publications);
    $this->view->publications = $publications;
    $this->view->quarterStats = $quarterStats;
    $this->view->byMonth = $byMonth;
    $this->view->date = $date;
    $this->view->date_next = $date_next;
    $this->view->date_previous = $date_previous;
    }

  // Import local csv of the snapshot of the old DB
  public function importAction()
    {
    $this->requireAdminPrivileges();
    $this->disableLayout();
    $this->disableView();
    if(!file_exists("/tmp/snapshot.csv")) return;

    $file = new SplFileObject("/tmp/snapshot.csv");
    $file->setFlags(SplFileObject::READ_CSV);
    foreach ($file as $row)
      {
      list($publication, $revision, $ip, $date) = $row;
      if(!is_numeric($publication))continue;
      $item = MidasLoader::loadComponent("Migration", "journal")->getItemByAllId($publication);
      if($item)
        {
        $ipLocationModel = MidasLoader::loadModel('IpLocation', 'statistics');
        $ipLocation = $ipLocationModel->getByIp($ip);
        if($ipLocation == false)
          {
          $ipLocation = MidasLoader::newDao('IpLocationDao', 'statistics');
          $ipLocation->setIp($ip);
          $ipLocation->setLatitude('');
          $ipLocation->setLongitude('');
          $ipLocationModel->save($ipLocation);
          }

        $download = MidasLoader::newDao('DownloadDao', 'statistics');
        $download->setItemId($item->getKey());
        $download->setIpLocationId($ipLocation->getKey());
        $download->setDate(date("Y-m-d H:i:s", strtotime($date)));
        $download->setUserAgent("");

        MidasLoader::loadModel('Download', 'statistics')->save($download);
        }
      }
    }

  private function getDownloads($date)
    {
    $start_date_formatted = $date."-01";
    $end_date_formatted = $date."-".date("t", strtotime($start_date_formatted));
    $db = Zend_Registry::get('dbAdapter');
    $results = $db->query("SELECT count(*) as total FROM statistics_download  WHERE date BETWEEN '".$start_date_formatted."' AND '".$end_date_formatted."' ")->fetchAll();
    if(count($results) != 0)
      {
      return $results[0]["total"];
      }
    return 0;

    }
  private function getPublications($date)
    {
    $start_date_formatted = $date."-01";
    $end_date_formatted = $date."-".date("t", strtotime($date));
    $db = Zend_Registry::get('dbAdapter');
    $results = $db->query("SELECT DISTINCT item_id FROM itemrevision WHERE date BETWEEN '".$start_date_formatted."' AND '".$end_date_formatted."' ")->fetchAll();

    $submitters = array();
    $count_noncode = 0;
    $count_code = 0;

    $publications = array();
    foreach($results as $res)
      {
      $item = MidasLoader::loadModel(('Item'))->load($res["item_id"]);
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      $revision = $resourceDao->getRevision();
      $code = false;
      foreach ($revision->getBitstreams() as $bitstream)
        {
        $type = MidasLoader::loadComponent("Bitstream", "journal")->getType($bitstream);
        if($type == BITSTREAM_TYPE_SOURCECODE || $type == BITSTREAM_TYPE_SOURCECODE_GITHUB)
          {
          $code = true;
          break;
          }
        }

      if($code)
        {
        $count_code++;
        }
      else
        {
        $count_noncode++;
        }

      $submitters[] = $revision->getUser()->getFullName();

      $pub = array();
      $pub['id'] = $resourceDao->getKey();;
      $pub['title'] = $resourceDao->getName();
      $pub['date'] = $revision->getDate();
      $pub['views'] = $resourceDao->getView();;
      $pub['downloads'] = $resourceDao->getDownload();

      $usLocale = new Zend_Locale('en_US');
      $zDate = new Zend_Date($pub['date']);
      $refDate = new Zend_Date('2014-12-01 01:00:00', $usLocale);

      $pub['licence'] = "N/A*";
      $pub['attribution'] = "N/A*";
      if($zDate->getTimestamp() > $refDate->getTimestamp())
        {
        $pub['licence'] = "No License Specified";
        $pub['attribution'] = "No";
        if($resourceDao->getSourceLicense() == OTJ_SOURCE_LICENSE_APACHE_2)
          {
          $pub['licence'] = "Apache 2";
          $pub['attribution'] = "Yes";
          }
        if($resourceDao->getSourceLicense() == OTJ_SOURCE_LICENSE_PUBLIC_DOMAIN)
          {
          $pub['licence'] = "Public Domain";
          }
        if($resourceDao->getSourceLicense() == OTJ_SOURCE_LICENSE_OTHER)
          {
          $pub['licence'] = "Other";
          }
        }

      $pub['licence'] .= " ".$refDate->getTimestamp();
      $pub['attribution'] .= " ".$zDate->getTimestamp();

      $publications[$pub['title']] = $pub;
      }
    $submitters = array_unique($submitters);
    sort($submitters);
    if(strtotime($date) < strtotime('08/01/2014'))
      {
      $count_noncode += $count_code;
      $count_noncode = $count_noncode. " (code + non code submissions)";
      $count_code = "-";
      }
    return array($count_code, $count_noncode, $submitters, $publications);
    }

  private function getTopDownloads($year,$month)
    {
    //Start date is the first day of the first month of the quarter
    $start_date_formatted = $year."-".$month."-01";
    // Go to the end of the quarter by increasing month by two
    $month=$month+2;
    //Stage most of the end date so that we can get the last day of the quarter
    $end_date = $year."-".$month;
    //append the last day of the last month of the quarter
    $end_date_formatted = $end_date."-".date("t", strtotime($end_date));
    $db = Zend_Registry::get('dbAdapter');
    $query = "SELECT item_id,COUNT(*) as 'num' FROM statistics_download WHERE date BETWEEN '". $start_date_formatted."'AND '". $end_date_formatted."' GROUP BY item_id";
    $results = $db->query($query)->fetchAll();

    $publications = array();
    foreach($results as $res)
      {
      $item = MidasLoader::loadModel(('Item'))->load($res["item_id"]);
      $resourceDao = MidasLoader::loadModel("Item")->initDao("Resource", $item->toArray(), "journal");
      $pub = array();
      $pub['id'] = $resourceDao->getKey();
      $pub['title'] = $resourceDao->getName();
      $pub['downloads'] = $res["num"];

      $publications[$pub['title']] = $pub;
      }
    foreach($publications as $key => $row)
    {
      $views[$key] = $row['downloads'];
    }

    array_multisort($views,SORT_DESC, $publications);
    $blah = array_slice($publications,0,10);
    return $blah;
  }
}//end class
