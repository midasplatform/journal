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

abstract class Journal_IssueModelBase extends Journal_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'journal_folder';
    $this->_key = 'folder_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'folder_id' =>  array('type' => MIDAS_DATA),
      
      );
    $this->initialize(); // required
    } // end __construct()

    
} // end class Validation_DashboardModelBase
