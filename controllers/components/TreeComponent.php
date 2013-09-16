<?php
/** Cateogyr Tree  component */
class Journal_TreeComponent extends AppComponent
{  
  /**
   * Get formatted tree
   * @param bool $includeDao include or nor the DAO 
   * (we don't want to do it if we plan to sent the array to the json format)
   * @return array
   */
  public function getAllTrees($includeDao = false, $selected = array())
    {    
    $trees = array();
    $allEntries = MidasLoader::loadModel('Category', 'journal')->getAll();
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