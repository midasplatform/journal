#---------------------------------------------------------------------------
# Copyright 2016 The Open Source Electronic Health Record Alliance
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#---------------------------------------------------------------------------

import sys
from  selenium import webdriver
from  selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
import argparse
import unittest
import re
import time

class test_otj(unittest.TestCase):

  @classmethod
  def tearDownClass(cls):
    global driver
    driver.close()

  def clickAndCheck(self,item,countClass):
      numElements = len(driver.find_elements_by_class_name(countClass))
      item.click()
      time.sleep(5);
      numElements_post = len(driver.find_elements_by_class_name(countClass))
      print "started with %s elements, found %s elements after" % (numElements,numElements_post)
      self.assertFalse(numElements == numElements_post)
      item.click()
      time.sleep(3);

  def test_text_search(self):
    searchbox = driver.find_element_by_id("live_search")
    searchbox.clear()
    searchbox.send_keys("test")
    self.clickAndCheck(driver.find_element_by_id("search_button"),"resourceLink")
    driver.find_element_by_id("clear_button").click()
    print searchbox
    print "bllah:"

  def test_category_filter(self):
    categoryTrees = driver.find_elements_by_class_name("dynatree-container")
    for tree in categoryTrees:
      self.clickAndCheck(tree.find_element_by_class_name("dynatree-checkbox"),"resourceLink")
    print "blah"

  def test_issue_switch(self):
    issueButtons = driver.find_elements_by_class_name("issueButton")
    for button in issueButtons[1:]:
      self.clickAndCheck(button,"resourceLink")
    print "even more blah"


if __name__ == '__main__':
  parser =argparse.ArgumentParser(description="Access the 'About' Text of the ViViaN(TM) webpage")
  parser.add_argument("-r",dest = 'webroot', required=True, help="Web root of the OTJ instance to test.")
  result = vars(parser.parse_args())
  driver = webdriver.Firefox()
  driver.get(result['webroot'])
  suite = unittest.TestLoader().loadTestsFromTestCase(test_otj)
  unittest.TextTestRunner(verbosity=2).run(suite)
