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


class Journal_BitstreamComponent extends AppComponent
{
  public function setType($bistream, $type)
    {
    if($bistream instanceof BitstreamDao && $bistream->saved && is_numeric($type))
      {
      $db = Zend_Registry::get('dbAdapter');
      $sql = "UPDATE `bitstream` set journalmodule_type = ".$type." where bitstream_id=".$bistream->getKey();
      $db->query($sql);
      }
    }
    
  public function getType($bistream)
    {
    if($bistream instanceof BitstreamDao && $bistream->saved)
      {
      $db = Zend_Registry::get('dbAdapter');
      $row = $db->fetchRow($db->select()
                ->from("bitstream", array("journalmodule_type"))
                ->where("bitstream_id = ?",  $bistream->getKey())
            );
      if(isset($row['journalmodule_type']) && is_numeric($row['journalmodule_type']))
        {
        return (int) $row['journalmodule_type'];
        }
      return false;
      }
    return false;
    }
} // end class




