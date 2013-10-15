<?php
/**
 * Category DAO
 */
class Reviewosehra_QuestionlistDao extends AppDao
  {
  public $_model = 'Questionlist';
  public $_module = 'reviewosehra';
  
  public function getTopics()
    {
    return $this->getModel()->getTopics($this);
    }  
    
  public function toArray()
    {    
    $array = array('list' => parent::toArray(), 'topics' => array());
    $topics = $this->getTopics();
    foreach($topics as $topic)
      {
      $questions = $topic->getQuestions();
      $topicArray = $topic->toArray();
      $topicArray['questions'] = array();
      foreach($questions as $question)
        {
        $questionArrayTmp = $question->toArray();
        $questionArrayTmp['attachfileValue'] = "";
        $questionArrayTmp['commentValue'] = "";
        $questionArrayTmp['value'] = "0";
        $topicArray['questions'][] = $questionArrayTmp;
        }
      $array['topics'][] = $topicArray;
      }
    return $array;
    }
  }
