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
class Reviewosehra_MigrateComponent extends AppComponent
{  

  public function migrateQuestionlist()
    {    
    $dao = MidasLoader::newDao('QuestionlistDao', 'reviewosehra');
    $dao->setName("Default Peer Migrated");
    $dao->setType(OSERHAREVIEW_LIST_PEERREVIEW);
    $dao->setDescription("");
    $dao->setCategoryId(-1);
    MidasLoader::loadModel("Questionlist", 'reviewosehra')->save($dao);
    
    $topic = MidasLoader::newDao('TopicDao', 'reviewosehra');
    $topic->setName("Compliant - Product Build Checklist");
    $topic->setDescription();
    $topic->setQuestionlistId($dao->getKey());
    MidasLoader::loadModel("Topic", 'reviewosehra')->save($topic);
    
    $listPeerAttestations = $this->getListPeerAttestations();
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-design-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-specification-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-usecase-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-interface-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-components-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-pds-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-testing-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-testing-documented-compliant']);
    $this->newQuestion($topic, $listPeerAttestations['compliant']['product-testing-pds-compliant']);
    
    $topic = MidasLoader::newDao('TopicDao', 'reviewosehra');
    $topic->setName("Compliant - Installation and Post-Installation");
    $topic->setDescription();
    $topic->setQuestionlistId($dao->getKey());
    MidasLoader::loadModel("Topic", 'reviewosehra')->save($topic);    
    $this->newQuestion($topic, $listPeerAttestations['compliant']['post-xindex-compliant']);
    
    $topic = MidasLoader::newDao('TopicDao', 'reviewosehra');
    $topic->setName("Functional - Before Patch Installation");
    $topic->setDescription();
    $topic->setQuestionlistId($dao->getKey());
    MidasLoader::loadModel("Topic", 'reviewosehra')->save($topic);    
    $this->newQuestion($topic, $listPeerAttestations['functional']['before-duplicate-functional']);
    $this->newQuestion($topic, $listPeerAttestations['functional']['before-defect-functional']);
    $this->newQuestion($topic, $listPeerAttestations['functional']['before-remedy-functional']);
    
    $topic = MidasLoader::newDao('TopicDao', 'reviewosehra');
    $topic->setName("Functional - Installation and Post-Installation");
    $topic->setDescription();
    $topic->setQuestionlistId($dao->getKey());
    MidasLoader::loadModel("Topic", 'reviewosehra')->save($topic);    
    $this->newQuestion($topic, $listPeerAttestations['functional']['post-data-functional']);
    $this->newQuestion($topic, $listPeerAttestations['functional']['post-capture-functional']);
    $this->newQuestion($topic, $listPeerAttestations['functional']['post-original-functional']);
    
    $topic = MidasLoader::newDao('TopicDao', 'reviewosehra');
    $topic->setName("Safe - Installation and Post-Installation");
    $topic->setDescription();
    $topic->setQuestionlistId($dao->getKey());
    MidasLoader::loadModel("Topic", 'reviewosehra')->save($topic);    
    $this->newQuestion($topic, $listPeerAttestations['safe']['post-install-safe']);
    $this->newQuestion($topic,  $listPeerAttestations['safe']['post-installpatch-safe']);
    $this->newQuestion($topic, $listPeerAttestations['safe']['post-icrs-safe']);
    $this->newQuestion($topic,  $listPeerAttestations['safe']['post-break-safe']);
    $this->newQuestion($topic, $listPeerAttestations['safe']['post-break-namespace']);   
    }
    
  private function newQuestion($topic, $description)
    {
    $dao = MidasLoader::newDao('QuestionDao', 'reviewosehra');
    $dao->setTopicId($topic->getKey());
    $dao->setDescription($description);
    $dao->setComment(1);
    $dao->setAttachfile(0);
    MidasLoader::loadModel("Question", 'reviewosehra')->save($dao);
    return $dao;
    }

  /** get list of elements*/
  public function getListPeerAttestations()
    {
    $return = array();
    $return['compliant']['product-design-compliant'] = "Does the technical documentation adequately describe the system requirements so that you know what to expect with the submitted code?";
    $return['compliant']['product-specification-compliant'] = "Does the technical documentation adequately describe how to install, test and use the submission?";
    $return['compliant']['product-usecase-compliant'] = "Are appropriate Use Cases documented?";
    $return['compliant']['product-interface-compliant'] = "Are all external interfaces adequately documented? ";
    $return['compliant']['product-components-compliant'] = "Are all external dependencies identified?";
    $return['compliant']['product-pds-compliant'] = "Do all submitted components follow appropriate community standards, e.g. SAC?";
    $return['compliant']['product-testing-compliant'] = "Is appropriate testing defined for the submission? ";
    $return['compliant']['product-testing-documented-compliant'] = "Are the expected results of testing documented, or captured in the automated tests?";
    $return['compliant']['product-testing-pds-compliant'] = "Does testing adequately address the product requirements?";
    
    /*
    $return['compliant']['product-testing-scripts-compliant'] = "Have the Test Scripts been completed?";
    $return['compliant']['product-testing-scripts-pds-compliant'] = "Do the Test Scripts conform to Product Development Standards?";
    $return['compliant']['product-mtp-compliant'] = "Is the Master Test Plan completed according to the Test Preparation process?";
    $return['compliant']['product-documentation-compliant'] = "Is Product Documentation available for the build?";
    $return['compliant']['product-tpt-compliant'] = "Has the sequence of integration of the Product Components been identified (see Test Preparation Process)? And Documented?";
    */
    
    //$return['compliant']['before-capture-compliant'] = "Capture the before routine checksums (SHA1) included in the patch before installing the patch to verify against the before checksums listed in National Patch Module (NPM), if applicable.";
    //$return['compliant']['before-xindex-compliant'] = "Run XINDEX on the patch build checking for warnings or errors. Print more than compiled errors and warnings, and accept the defaults. ( done by running the CTest testing suite). Ensure there are no issues reported.";

    $return['compliant']['post-xindex-compliant'] = "After installing the patch, are there new XINDEX errors reported by the OSEHRA automated testing framework?";
    //$return['compliant']['post-code-compliant'] = "Review the code ensuring its accuracy and meets the current <b>Standards and Conventions</b> (SAC) standards.";


    //$return['functional']['before-duplicate-functional'] = "Duplicate the original defect as described in the Remedy ticket(s) listed in the patch.";
    $return['functional']['before-duplicate-functional'] = "Do the tests supplied with the submission demonstrate appropriate system behavior prior to installing the new contribution? For remediation submissions, does the testing demonstrate the defect(s) prior to applying the new submission?";
    $return['functional']['before-defect-functional'] = "For remediation and refactoring submissions, is the analysis of the defect(s) accurate; is the solution acceptable and appropriate; and does the solution appear to be the most effective way to resolve the defect(s) without introducing new defects?";
    $return['functional']['before-remedy-functional'] = "For remediation and refactoring submissions, does the submission clearly describe all the applicable issues and their solutions?";

    $return['functional']['post-data-functional'] = "Are affected data, cross-references, and outputs, appropriate after installation? ";
    $return['functional']['post-capture-functional'] = "Do the supplied tests run and provide the expected results as documented? ";
    $return['functional']['post-original-functional'] =  "For remediation submissions, has the defect been removed as indicated in the documentation?";
    
    //$return['safe']['before-capture-safe'] =  "Capture the before portions of the Data Dictionaries (DDs) affected by the patch, for comparison to after install, if applicable.";

    $return['safe']['post-install-safe'] =  "Does the Technical Documentation adequately describe the installation procedures, and do they work as described? ";
    //$return['safe']['post-capture-safe'] =  "Capture the after routine checksums (from OTJ-Git SHA1) after installing the patch.";
    //$return['safe']['post-checksums-safe'] =  "Verify the after patch checksums (SHA1) match what is documented in the patch description in the NPM.";
    //$return['safe']['post-dds-safe'] =  "Capture and verify the after portions of the DDs to ensure the changes were made appropriately.";
    $return['safe']['post-installpatch-safe'] =  "Does the submitted code install correctly?";
    $return['safe']['post-icrs-safe'] =  "Are there any unexplained or unnecessary modifications to existing tests, or expected results?";
    $return['safe']['post-break-safe'] =  "Do all pre-existing tests continue to work after the installation of the submitted code?";
    $return['safe']['post-break-namespace'] =  "Does the submitted code conform to community requirements regarding namespacing and use of global variables?";
   
    return $return;
    }
  /** get list of elements*/
  public function getListFinalAttestations()
    {
    $return = array();;
    $return['compliant']['final-product-build-documentation'] = "Does the provided documentation adequately describe the intended behavior of the software?";
    $return['compliant']['final-product-build-usecases'] = "Are the Use Cases documented?";
    $return['compliant']['final-product-build-interfaces'] = "Are external interfaces documented?";
    $return['compliant']['final-product-build-components'] = "Are the components required for the build identified?";
    $return['compliant']['final-product-build-conventions'] = "Do all components follow appropriate standards and conventions?";
    $return['compliant']['final-product-build-tests'] = "Are there sufficient unit and regression tests available to verify the submission?";
    $return['compliant']['final-product-build-how-install'] = "Does the documentation describe how to install, test and run the code? ";
    $return['compliant']['final-product-build-functionnal-tests'] = "Are there functional tests available for this submission?";
    
    $return['compliant']['final-before-duplication'] = "Is test plan/problem duplication available, if applicable?";
    $return['compliant']['final-before-tests'] = "Do the unit and regression tests behave appropriately prior to product installation?";
    $return['compliant']['final-before-functionnal-tests'] = "Do the functional tests behave appropriately prior to product installation?";  
    
    $i = 0;
    $return['compliant']['final-mcode-description-'.$i++.''] = "Perform editorial review";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure patch subject is clear";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure acronyms are defined in the first occurrence";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Run spelling and grammar check";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure all required issue ticket numbers are listed (JIRA, Remedy, CA Service Desk, etc.)";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure patch priority is listed (Emergency or Routine via FORUM for VA patches)";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure patch category(s) are listed (patch for a patch, etc. via FORUM for VA patches)";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure instructions for disabling options/protocols are included if required";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Confirm there is a statement regarding whether or not users can be on the system";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure time required to install patch is included";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Question manual deletion of routines";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Validate accuracy of patch name(s) found in the description";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure format is correct for files: FILE NAME (#number)";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure format is correct for fields: FIELD NAME (#number)";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure format is correct for options: Menu Text [INTERNAL OPTION NAME]";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure patch number list is in order of release";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure full name is used for other packages such as VA FileMan, TaskMan, MailMan, etc.";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure dependencies on other packages/patches listed";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure database changes are documented ";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Verify the patch description to the system description";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure estimate of disk space and journal file consumption is included, if applicable";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Ensure reference is made to any documentation that will be sent out separately, if applicable";
    $return['compliant']['final-mcode-description-'.$i++.''] = "Compare the Installation Guide to the patch description's installation instructions, if applicable.";

    $i = 0;
    $return['compliant']['final-mcode-capture-'.$i++] = "Check for use of privileged access (R, W, P/D)";
    $return['compliant']['final-mcode-capture-'.$i++] = "Check for current nodes";
    
    $i = 0;
    $return['compliant']['final-mcode-components-'.$i++] = "Data Dictionary";
    $return['compliant']['final-mcode-components-'.$i++] = "Data Values";
    $return['compliant']['final-mcode-components-'.$i++] = "Protocols";
    $return['compliant']['final-mcode-components-'.$i++] = "Options";
    $return['compliant']['final-mcode-components-'.$i++] = "Duplicate problem and save the results, if applicable";
    
    $i = 0;
    $return['compliant']['final-mcode-installation-'.$i++] = "Load the patch";
    $return['compliant']['final-mcode-installation-'.$i++] = "Verify checksums in Transport Global";
    
    $i = 0;
    $return['compliant']['final-mcode-print-'.$i++] = "Confirm Type ? Single Package";
    $return['compliant']['final-mcode-print-'.$i++] = "Confirm presence of appropriate Required Builds";
    $return['compliant']['final-mcode-print-'.$i++] = "Confirm all expected routines are included in the build (listed in the routine multiple)";
    $return['compliant']['final-mcode-print-'.$i++] = "Confirm exported routine list is correctly namespaced";
    $return['compliant']['final-mcode-print-'.$i++] = "Confirm alpha/beta testing is blank or set to ?no?";
    $return['compliant']['final-mcode-print-'.$i++] = "Compare Transport Global to Current System";
    $return['compliant']['final-mcode-print-'.$i++] = "Back a Transport Global";
    $return['compliant']['final-mcode-print-'.$i++] = "Install Package(s)";
    $return['compliant']['final-mcode-print-'.$i++] = "Compare time required to install with installation instructions";
    $return['compliant']['final-mcode-print-'.$i++] = "Environment check, if applicable";

    $return['compliant']['final-post-tests'] = "Do the unit and regression tests pass?";
    $return['compliant']['final-post-functionnal-tests'] = "Do the functional tests pass?";
    
    $return['compliant']['final-post-checksums'] = "Verify the \"after\" patch installation checksums, if applicable";
    $return['compliant']['final-post-check'] = "Perform ^%RCHECK on all patch routines and save results, if applicable";

    $i = 0;
    $return['compliant']['final-post-routines-'.$i++.''] = "First line";
    $return['compliant']['final-post-routines-'.$i++.''] = "Second line";
    $return['compliant']['final-post-routines-'.$i++.''] = "Third line";
    $return['compliant']['final-post-routines-'.$i++.''] = "Save copy of \"after\" components (other than routines), if applicable";
    $return['compliant']['final-post-routines-'.$i++.''] = "Ensure there are no variables, new with this patch, that were not explicitly killed.";
    $return['compliant']['final-post-routines-'.$i++.''] = "Ensure there are no warnings or errors listed";
    $return['compliant']['final-post-routines-'.$i++.''] = "Ensure exported protocols are properly attached to menus, if applicable";
    $return['compliant']['final-post-routines-'.$i++.''] = "Ensure exported options are properly attached to menus, if applicable";

    $i = 0;
    $return['compliant']['final-post-file-'.$i++.''] = "Save a copy of \"after\" components (other than routines), if applicable";
    $return['compliant']['final-post-file-'.$i++.''] = "Confirm no erroneous nodes are exported";
    $return['compliant']['final-post-file-'.$i++.''] = "If patch alters data, verify that new values adhere to data dictionaries";
    $return['compliant']['final-post-file-'.$i++.''] = "Save copy of \"after\" components (other than routines), if applicable";
    
    $i = 0;
    $return['compliant']['final-post-rfind-'.$i++.''] = "////";
    $return['compliant']['final-post-rfind-'.$i++.''] = "DIC(0)";
    $return['compliant']['final-post-rfind-'.$i++.''] = "^UTILITY";
    $return['compliant']['final-post-rfind-'.$i++.''] = "^TMP";
    $return['compliant']['final-post-rfind-'.$i++.''] = "^XTMP";
    $return['compliant']['final-post-rfind-'.$i++.''] = "%";
    $return['compliant']['final-post-rfind-'.$i++.''] = '$I';
    $return['compliant']['final-post-rfind-'.$i++.''] = "U=";
    $return['compliant']['final-post-rfind-'.$i++.''] = "K^10. ^(";

    $return['compliant']['final-post-io'] = "Review sets and kills of IO variables";
    $return['compliant']['final-post-executable'] = "Confirm fields which contain executable code are write protected in the DD with ?@? or are defined as VA FileMan data type of ?M?, if applicable.";
    $return['compliant']['final-post-patch'] = "Test the patch to confirm that the patch has corrected the problem, if applicable";
    $return['compliant']['final-post-xter'] = "Check error log (D ^XTER)";
    $return['compliant']['final-post-manager'] = "Deliver report of findings to the developer and development manager";
    $return['compliant']['final-post-jira'] = "Ensure Patch Tracking Message (JIRA) has been delivered to appropriate staff, if applicable.";
    $return['compliant']['final-post-documentation'] = "Perform user documentation review, if applicable";
    $return['compliant']['final-post-filename'] = "Ensure the documentation file names in the patch description match the actual file names, if applicable";

    return $return;
    }
    

      /** get list of elements*/
  public function getListPeerAttestations2()
    {
    $return = array();
    $return['compliant']['before-capture-compliant2'] = "Capture the before routine checksums (SHA1) included in the patch before installing the patch to verify against the before checksums listed in National Patch Module (NPM), if applicable.";
    $return['compliant']['before-xindex-compliant2'] = "Run XINDEX on the patch build checking for warnings or errors. Print more than compiled errors and warnings, and accept the defaults. ( done by running the CTest testing suite). Ensure there are no issues reported.";

    $return['compliant']['post-xindex-compliant2'] = "Run XINDEX on the build after installing the patch checking for warnings or errors. Print more than compiled errors and warnings, and accept the defaults. You may answer No to the Print routines option.";
    $return['compliant']['post-code-compliant2'] = "Review the code ensuring its accuracy and meets the current <b>Standards and Conventions</b> (SAC) standards.";

    $return['compliant']['product-design-compliant2'] = "Do all components follow the System Design Document?";
    $return['compliant']['product-specification-compliant2'] = "Do all components satisfy the requests of the Requirements Specification Document?";
    $return['compliant']['product-usecase-compliant2'] = "Are the Use Case Specifications documented?";
    $return['compliant']['product-interface-compliant2'] = "Is the Interface Control Document complete and current?";
    $return['compliant']['product-components-compliant2'] = "Are the components required for the build identified?";
    $return['compliant']['product-pds-compliant2'] = "Do all components follow Product Development Standards?";
    $return['compliant']['product-testing-compliant2'] = "Is Product Component Testing (aka Unit Testing) complete for each component of the build?";
    $return['compliant']['product-testing-documented-compliant2'] = "Are the results of the Product Component Testing documented?";
    $return['compliant']['product-testing-pds-compliant2'] = "Did Product Component Testing follow Product Development Standards?";
    $return['compliant']['product-testing-scripts-compliant2'] = "Have the Test Scripts been completed?";
    $return['compliant']['product-testing-scripts-pds-compliant2'] = "Do the Test Scripts conform to Product Development Standards?";
    $return['compliant']['product-mtp-compliant2'] = "Is the Master Test Plan completed according to the Test Preparation process?";
    $return['compliant']['product-documentation-compliant2'] = "Is Product Documentation available for the build?";
    $return['compliant']['product-tpt-compliant2'] = "Has the sequence of integration of the Product Components been identified (see Test Preparation Process)? And Documented?";

    $return['functional']['before-duplicate-functional2'] = "Duplicate the original defect as described in the Remedy ticket(s) listed in the patch.";
    $return['functional']['before-defect-functional2'] = "Confirm the analysis of the defect(s) is accurate; the solution is acceptable and appropriate, and appears to be the most effective way to resolve the defect(s) without introducing new defects.";
    $return['functional']['before-remedy-functional2'] = "Review all Remedy tickets and check that the patch description clearly describes all issues and solutions.";

    $return['functional']['post-data-functional2'] = "Review affected data, cross-references, and outputs, if applicable.";
    $return['functional']['post-capture-functional2'] = "Capture and perform unit testing to verify that the code changes work and provide the unit testing results to SQA.";
    $return['functional']['post-original-functional2'] =  "Verify the original issue(s) is fixed and options, menus, security keys, protocols, etc. are in the patch as noted in the patch description.";

    $return['safe']['before-capture-safe2'] =  "Capture the before portions of the Data Dictionaries (DDs) affected by the patch, for comparison to after install, if applicable.";

    $return['safe']['post-install-safe2'] =  "Install the patch using the installation instructions included in the patch description or Installation Guide, as applicable, confirming the instructions are correct.";
    $return['safe']['post-capture-safe2'] =  "Capture the after routine checksums (from OTJ-Git SHA1) after installing the patch.";
    $return['safe']['post-checksums-safe2'] =  "Verify the after patch checksums (SHA1) match what is documented in the patch description in the NPM.";
    $return['safe']['post-dds-safe2'] =  "Capture and verify the after portions of the DDs to ensure the changes were made appropriately.";
    $return['safe']['post-installpatch-safe2'] =  "Confirm the patch installed correctly.";
    $return['safe']['post-icrs-safe2'] =  "Confirm that Integration Control Registrations (ICRs) supported by the package have been updated if the code has changed and the subscribing packages have been informed of the changes, if applicable.";
    $return['safe']['post-break-safe2'] =  "Verify that the patch does not break existing functionality.";

    return $return;
    }

  /** get list of elements*/
  public function getListFinalAttestations2()
    {
    $return = array();;
    $return['compliant']['final-product-design-compliant2'] = "Do all components follow the System Design Document?";
    $return['compliant']['final-product-specification-compliant2'] = "Do all components satisfy the requests of the Requirements Specification Document?";
    $return['compliant']['final-product-usecase-compliant2'] = "Are the Use Case Specifications documented?";
    $return['compliant']['final-product-interface-compliant2'] = "Is the Interface Control Document complete and current?";
    $return['compliant']['final-product-components-compliant2'] = "Are the components required for the build identified?";
    $return['compliant']['final-product-pds-compliant2'] = "Do all components follow Product Development Standards?";
    $return['compliant']['final-product-testing-compliant2'] = "Is Product Component Testing (aka Unit Testing) complete for each component of the build?";
    $return['compliant']['final-product-testing-documented-compliant2'] = "Are the results of the Product Component Testing documented?";
    $return['compliant']['final-product-testing-pds-compliant2'] = "Did Product Component Testing follow Product Development Standards?";
    $return['compliant']['final-product-testing-scripts-compliant2'] = "Have the Test Scripts been completed?";
    $return['compliant']['final-product-testing-scripts-pds-compliant2'] = "Do the Test Scripts conform to Product Development Standards?";
    $return['compliant']['final-product-mtp-compliant2'] = "Is the Master Test Plan completed according to the Test Preparation process?";
    $return['compliant']['final-product-documentation-compliant2'] = "Is Product Documentation available for the build?";
    $return['compliant']['final-product-tpt-compliant2'] = "Has the sequence of integration of the Product Components been identified (see Test Preparation Process)? And Documented?";
    $return['compliant']['final-product-integration-testing-compliant2'] = "Has Component Integration testing been performed?";
    $return['compliant']['final-product-integration-defect-compliant2'] = "Has the Component Integration Test Defect Log been completed?";
    $return['compliant']['final-product-integration-evaluation-compliant2'] = "Has the Component Integration Test Evaluation Summary been completed?";
    $return['compliant']['final-product-integration-execution-compliant2'] = "Has the Component Integration Test Execution Log been completed?";
    $return['compliant']['final-product-sqa-compliant2'] = "Has the Software Quality Assurance Review Checklist been started?";

    $i = 0;
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Perform editorial review";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure patch subject is clear";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure acronyms are defined in the first occurrence";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Run spelling and grammar check";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure all required Remedy JIRA ticket(s) are listed";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure patch priority is listed";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure patch category(s) are listed";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure instructions for disabling options/protocols are included";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Confirm there is a statement regarding whether or not users can be on the system";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure time required to install patch is included";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Question manual deletion of routines";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Validate accuracy of patch name(s) found in the description";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure format is correct for files: FILE NAME (#number)";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure format is correct for fields: FIELD NAME (#number)";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure format is correct for options: Menu Text [INTERNAL OPTION NAME]";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure patch number list is in order of release";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure full name is used for other packages such as VA FileMan, TaskMan, MailMan, etc.";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure dependencies on other packages/patches listed";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure database changes have been approved by database administrator";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "If this is an ENHANCEMENT type patch, compare the description to the New Service Request (NSR), if applicable";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure estimate of disk space and journal file consumption is included, if applicable";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure reference is made to any documentation that will be sent out separately, if applicable";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Compare the Installation Guide to the patch description's installation instructions, if applicable.";
    $return['compliant']['final-before-description-'.$i++.'-compliant2'] = "Ensure Patient Safety Issues (PSIs) addressed in this patch are identified to include the PSI number.";

    $return['compliant']['final-before-duplication-compliant2'] = "Ensure test plan/problem duplication is available, if applicable";
    $i = 0;
    $return['compliant']['final-before-remedy-'.$i++.'-compliant2'] = "Ensure status field is ?Work in Progress? and the Pending Field is empty";
    $return['compliant']['final-before-remedy-'.$i++.'-compliant2'] = "Ensure developer has posted a note for patch reference";
    $return['compliant']['final-before-remedy-'.$i++.'-compliant2'] = "Ensure TeamPlay info is in Keywords section (TeamPlay ID.WBS)";

    $return['compliant']['final-before-patch-compliant2'] = "Understand the problem or new features of the patch";
    $return['compliant']['final-before-index-compliant2'] = "Perform ^INDEX";
    $return['compliant']['final-before-checksums-compliant2'] = "Collect the before patch installation checksum(s), if applicable";
    $return['compliant']['final-before-blood-bank-compliant2'] = "Ensure that modifications of routines and files have been reviewed by the Blood Bank Team for potential effects on Blood Bank software in accordance with Directive 2007-038, if applicable";
    $return['compliant']['final-before-hl7-compliant2'] = "If Health Level Seven (HL7) segments are included, check that new segment approval has been obtained from the Message Administration";
    $return['compliant']['final-before-mailman-compliant2'] = "Retrieve the MailMan message/host file, if applicable [Too VA specific ?]";

    $i = 0;
    $return['compliant']['final-before-check-file-'.$i++.'-compliant2'] = "Check for use of privileged access (R, W, P/D)";
    $return['compliant']['final-before-check-file-'.$i++.'-compliant2'] = "Check for current nodes";
    $i = 0;
    $return['compliant']['final-before-check-components-'.$i++.'-compliant2'] = "Data Dictionary";
    $return['compliant']['final-before-check-components-'.$i++.'-compliant2'] = "Data Values";
    $return['compliant']['final-before-check-components-'.$i++.'-compliant2'] = "Protocols";
    $return['compliant']['final-before-check-components-'.$i++.'-compliant2'] = "Options";

    $return['compliant']['final-before-duplicate-save-compliant2'] = "Duplicate problem and save the results, if applicable";

    $return['compliant']['final-installation-load-compliant2'] = "Load the patch";
    $return['compliant']['final-installation-checksums-compliant2'] = "Verify checksums in Transport Global";

    $i = 0;
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm Type ? Single Package";
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm National Tracking = Yes";
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm National Package = package of patch";
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm presence of appropriate Required Builds";
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm all expected routines are included in the build (listed in the routine multiple)";
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm exported routine list is correctly namespaced";
    $return['compliant']['final-installation-print-'.$i++.'-compliant2'] = "Confirm alpha/beta testin is blank or set to ?no?";


    $return['compliant']['final-installation-compare-transport-global-compliant2'] = "Compare Transport Global to Current System";
    $return['compliant']['final-installation-back-transport-global-compliant2'] = "Back a Transport Global";
    $return['compliant']['final-installation-package-compliant2'] = "Install Package(s)";
    $return['compliant']['final-installation-time-compliant2'] = "Compare time required to install with installation instructions";
    $return['compliant']['final-installation-env-compliant2'] = "Environment check, if applicable";

    $return['compliant']['final-post-checksums-compliant2'] = "Verify the \"after\" patch installation checksums, if applicable";
    $return['compliant']['final-post-rcheck-compliant2'] = "Perform ^%RCHECK on all patch routines and save results, if applicable";

    $i = 0;
    $return['compliant']['final-post-routines-'.$i++.'-compliant2'] = "First line";
    $return['compliant']['final-post-routines-'.$i++.'-compliant2'] = "Second line";
    $return['compliant']['final-post-routines-'.$i++.'-compliant2'] = "Third line";

    $return['compliant']['final-post-spell-compliant2'] = "Spell check routines for anything displayed to the user";

    $i = 0;
    $return['compliant']['final-post-index-'.$i++.'-compliant2'] = "Ensure there are no variables, new with this patch, that were not explicitly killed.";
    $return['compliant']['final-post-index-'.$i++.'-compliant2'] = "Ensure there are no warnings or errors listed";


    $return['compliant']['final-post-agreements-compliant2'] = "Check for necessary integration agreements";
    $return['compliant']['final-post-protocols-compliant2'] = "Ensure exported protocols are properly attached to menus, if applicable";
    $return['compliant']['final-post-options-compliant2'] = "Ensure exported options are properly attached to menus, if applicable";

    $i = 0;
    $return['compliant']['final-post-file-'.$i++.'-compliant2'] = "Save a copy of \"after\" components (other than routines), if applicable";
    $return['compliant']['final-post-file-'.$i++.'-compliant2'] = "Confirm no erroneous nodes are exported";
    $return['compliant']['final-post-file-'.$i++.'-compliant2'] = "If patch alters data, verify that new values adhere to data dictionaries";

    $return['compliant']['final-post-spell-compliant2'] = "Save copy of \"after\" components (other than routines), if applicable";

    $i = 0;
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "////";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "DIC(0)";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "^UTILITY";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "^TMP";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "^XTMP";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "%";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = '$I';
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "U=";
    $return['compliant']['final-post-rfind-'.$i++.'-compliant2'] = "K^10. ^(";

    $return['compliant']['final-post-io-compliant2'] = "Review sets and kills of IO variables";
    $return['compliant']['final-post-executable-compliant2'] = "Confirm fields which contain executable code are write protected in the DD with ?@? or are defined as VA FileMan data type of ?M?, if applicable.";
    $return['compliant']['final-post-patch-compliant2'] = "Test the patch to confirm that the patch has corrected the problem, if applicable";
    $return['compliant']['final-post-xter-compliant2'] = "Check error log (D ^XTER)";
    $return['compliant']['final-post-manager-compliant2'] = "Deliver report of findings to the developer and development manager";
    $return['compliant']['final-post-jira-compliant2'] = "Ensure Patch Tracking Message (JIRA) has been delivered to appropriate staff, if applicable.";
    $return['compliant']['final-post-documentation-compliant2'] = "Perform user documentation review, if applicable";
    $return['compliant']['final-post-filename-compliant2'] = "Ensure the documentation file names in the patch description match the actual file names, if applicable";

    return $return;
    }

} // end class