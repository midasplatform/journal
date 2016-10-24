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

/** Cateogyr Tree  component */
class Journal_TreeComponent extends AppComponent
{
  /**
   * Get formatted tree
   * @param bool $includeDao include or nor the DAO
   * (we don't want to do it if we plan to sent the array to the json format)
   * @return array
   */
  public function getAllTrees($includeDao = false, $selected = array(), $showFilters = false)
    {
    $trees = array();
    $allEntries = MidasLoader::loadModel('Category', 'journal')->getAll();

    $cacheFile = UtilityComponent::getTempDirectory()."/treeCache.json";

    if(empty($selected) && file_exists($cacheFile) &&  (filemtime($cacheFile) > (time() - 60 * 60 * 24 * 1 ))) // 1 day cache
      {
      $trees = JsonComponent::decode(file_get_contents($cacheFile));
      }
    else
      {
      foreach($allEntries as $entry)
        {
        if($entry->getParentId() == -1)
          {
          $select = 0;
          if(in_array($entry->getKey(), $selected)) $select = 1;
          if($includeDao) $trees[] = array('dao' => $entry, 'select' => $select, 'title' => $entry->getName(), 'key' => $entry->getKey(),
              'children' => $this->getChildren($allEntries, $entry, $includeDao, $selected));
          else $trees[] = array('title' => $entry->getName(), 'select' => $select, 'key' => $entry->getKey(),
              'children' => $this->getChildren($allEntries, $entry, $includeDao, $selected));
          }
        }
      if(empty($selected)) file_put_contents($cacheFile, JsonComponent::encode($trees));
      }

    if($showFilters)
      {
      $trees[] = array('dao' => new stdClass(), 'select' => 0, 'title' => "Certified", 'key' => -1,
            'children' => array(
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Level 1", 'key' => "certified-1",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Level 2", 'key' => "certified-2",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Level 3", 'key' => "certified-3",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Level 4", 'key' => "certified-4",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "With peer reviews", 'key' => "with_review",
                'children' => array())
            ));

      $trees[] = array('dao' => new stdClass(), 'select' => 0, 'title' => "Code", 'key' => -1,
            'children' => array(
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Code in Flight", 'key' => "code_in_flight",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "With code", 'key' => "with_code",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "With testing code", 'key' => "with_test_code",
                'children' => array())
            ));

      $core=OSEHRAREVIEW_TYPE_CORE;
      $component=OSEHRAREVIEW_TYPE_COMPONENT;
      $trees[] = array('dao' => new stdClass(), 'select' => 0, 'title' => "OSEHRA VistA", 'key' => -1,
            'children' => array(
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Core", 'key' => "submission_type-$core",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "Certified Component", 'key' => "submission_type-$component",
                'children' => array())
            ));

      $apache2 = OTJ_SOURCE_LICENSE_APACHE_2;
      $public_domain = OTJ_SOURCE_LICENSE_PUBLIC_DOMAIN;
      $other = OTJ_SOURCE_LICENSE_OTHER;
      $gpl = OTJ_SOURCE_LICENSE_GPL;
      $lgpl = OTJ_SOURCE_LICENSE_LGPL;
      $bsd = OTJ_SOURCE_LICENSE_BSD;
      $none = OTJ_SOURCE_LICENSE_NOT_DEFINED;
      $trees[] = array('dao' => new stdClass(), 'select' => 0, 'title' => "License", 'key' => -1,
            'children' => array(
                array('dao' => new stdClass(), 'select' => 1, 'title' => "Apache 2", 'key' => "license-$apache2",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 1, 'title' => "Public Domain", 'key' => "license-$public_domain",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 1, 'title' => "Other", 'key' => "license-$other",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "GPL (Any Version)", 'key' => "license-$gpl",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "LGPL (Any Version)", 'key' => "license-$lgpl",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 0, 'title' => "BSD", 'key' => "license-$bsd",
                'children' => array()),
                array('dao' => new stdClass(), 'select' => 1, 'title' => "No License Specified", 'key' => "license-$none",
                'children' => array())
            ));
      }
    return $trees;
    }

  /**
   * Get childens recursively
   * @param array $tree
   * @param CategoryDao $parent
   * @param bool $includeDao include or nor the DAO
   * @return array
   */
  private function getChildren($tree, $parent, $includeDao, $selected)
    {
    $children = array();
    foreach($tree as $entry)
      {
      if($entry->getParentId() == $parent->getKey())
        {
        $select = 0;
        if(in_array($entry->getKey(), $selected)) $select = 1;
        if($includeDao) $children[] = array('dao' => $entry, 'select' => $select, 'title' => $entry->getName(), 'key' => $entry->getKey(),
            'children' => $this->getChildren($tree, $entry, $includeDao, $selected));
        else $children[] = array('title' => $entry->getName(), 'select' => $select, 'key' => $entry->getKey(),
            'children' => $this->getChildren($tree, $entry, $includeDao, $selected));
        }
      }
    return $children;
    }
} // end class
