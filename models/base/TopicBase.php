<?php

abstract class Reviewosehra_TopicModelBase extends Reviewosehra_AppModel {

  /** constructor */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'reviewosehra_topic';
    $this->_key = 'topic_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'topic_id' => array('type' => MIDAS_DATA),
        'questionlist_id' => array('type' => MIDAS_DATA),
        'position' => array('type' => MIDAS_DATA),
        'name' => array('type' => MIDAS_DATA),
        'description' => array('type' => MIDAS_DATA),
        'comment' => array('type' => MIDAS_DATA),
        'attachfile' => array('type' => MIDAS_DATA),
        'questions' => array('type' => MIDAS_ONE_TO_MANY, 'model' => 'Question', 'module' => "reviewosehra", 'parent_column' => 'topic_id', 'child_column' => 'topic_id'),
        'questionlist' => array('type' => MIDAS_MANY_TO_ONE, 'model' => 'Questionlist', 'module' => "reviewosehra", 'parent_column' => 'questionlist_id', 'child_column' => 'questionlist_id'),
    );
    $this->initialize(); // required
    }
    
  public function save($dao)
    {
    if(!$dao->saved)
      {
      $list = $dao->getQuestionlist();
      $topics = $list->getTopics();
      if(empty($topics))
        {
        $dao->setPosition(1);
        }
      else
        {
        $lasttopic = end($topics);
        $dao->setPosition($lasttopic->getPosition() + 1);
        }
      }
    parent::save($dao);
    }

  function moveUp($dao)
    {
    $position = $dao->getPosition();
    $list = $dao->getQuestionlist();
    $topics = $list->getTopics();
    foreach($topics as $topic)
      {
      if($topic->getPosition() == ($position - 1))
        {
        $topic->setPosition($topic->getPosition() + 1);
        $this->save($topic);
        }
      }
    $dao->setPosition($dao->getPosition() - 1);
    $this->save($dao);
    }

  function moveDown($dao)
    {
    $position = $dao->getPosition();
    $list = $dao->getQuestionlist();
    $topics = $list->getTopics();
    foreach($topics as $topic)
      {
      if($topic->getPosition() == ($position + 1))
        {
        $topic->setPosition($topic->getPosition() - 1);
        $this->save($topic);
        }
      }
    $dao->setPosition($dao->getPosition() + 1);
    $this->save($dao);
    }

  public function delete($dao)
    {
    if (!$dao instanceof Reviewosehra_TopicDao)
      {
      throw new Zend_Exception("Deleting a list requires a valid instance of an Reviewosehra_TopicDao.");
      }
    $questions = $dao->getQuestions();

    foreach ($questions as $question)
      {
      MidasLoader::loadModel('Question', 'reviewosehra')->delete($question);
      }
    parent::delete($dao);
    }

// delete
}

// end class Validation_DashboardModelBase
