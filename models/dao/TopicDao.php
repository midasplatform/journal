<?php
/**
 * Category DAO
 */
class Reviewosehra_TopicDao extends AppDao
  {
  public $_model = 'Topic';
  public $_module = 'reviewosehra';
  
  public function getQuestions()
    {
    return $this->getModel()->getQuestions($this);
    }  
  }
