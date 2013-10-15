<?php
abstract class Reviewosehra_QuestionModelBase extends Reviewosehra_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'reviewosehra_question';
    $this->_key = 'question_id';

    /** This initialize automatically the getters and setters */
    $this->_mainData = array(
        'question_id' =>  array('type' => MIDAS_DATA),
        'topic_id' => array('type' => MIDAS_DATA),
        'description' => array('type' => MIDAS_DATA),
        'comment' => array('type' => MIDAS_DATA),
        'attachfile' => array('type' => MIDAS_DATA),
        'position' => array('type' => MIDAS_DATA),
        'topic' => array('type' => MIDAS_MANY_TO_ONE, 'model' => 'Topic', 'module' => "reviewosehra", 'parent_column' => 'topic_id', 'child_column' => 'topic_id'),
        );
    $this->initialize(); // required
    } // end __construct()

  public function save($dao)
    {
    if(!$dao->saved)
      {
      $list = $dao->getTopic();
      $questions = $list->getQuestions();
      if(empty($questions))
        {
        $dao->setPosition(1);
        }
      else
        {
        $question = end($questions);
        $dao->setPosition($question->getPosition() + 1);
        }
      }
    parent::save($dao);
    }
  
  function moveUp($dao)
    {
    $position = $dao->getPosition();
    $topic = $dao->getTopic();
    $questions = $topic->getQuestions();
    foreach($questions as $topic)
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
    $topic = $dao->getTopic();
    $questions = $topic->getQuestions();
    foreach($questions as $topic)
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
    
} // end class Validation_DashboardModelBase
