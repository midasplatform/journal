CREATE TABLE IF NOT EXISTS `reviewosehra_questionlist` (
  `questionlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `name` varchar(512) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`questionlist_id`)
)   DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reviewosehra_topic` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `questionlist_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `name` varchar(512) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`topic_id`)
)   DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reviewosehra_question` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL ,
  `position` int(11) NOT NULL,
  `description` text NOT NULL,
  `comment` tinyint(4) NOT NULL,
  `attachfile` tinyint(4) NOT NULL,
  PRIMARY KEY (`question_id`)
)   DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reviewosehra_review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `revision_id` int(11) NOT NULL ,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `content` text NOT NULL,
  `cache_summary` text NOT NULL,
  `complete` tinyint(4) NOT NULL,
  PRIMARY KEY (`review_id`)
)   DEFAULT CHARSET=utf8;