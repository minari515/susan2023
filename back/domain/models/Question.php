<?php
/**
 * 質疑応答情報のエンティティ
 */
class QuestionEntity {
  /**
   * @var int 質問リストのインデックス
   */
  public $index;
  /**
   * @var string 質問の投稿日時
   */
  public $timestamp;
  /**
   * @var int 対象講義回
   */
  public $lectureNumber;
  /**
   * @var string 質問内容
   */
  public $questionText;
  /**
   * @var string 回答内容
   */
  public $answerText;
  /**
   * @var bool 全体配信フラグ
   */
  public $broadcast;
  /**
   * @var string Dialogflowインテント名
   */
  public $intentName;

  public function __construct($index, $timestamp, $lectureNumber, $questionText, $answerText, $broadcast, $intentName) {
    $this->index = $index;
    $this->timestamp = $timestamp;
    $this->lectureNumber = $lectureNumber;
    $this->questionText = $questionText;
    $this->answerText = $answerText;
    $this->broadcast = (bool)$broadcast;
    $this->intentName = $intentName;
  }

  public function getIndex() {
    return $this->index;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }

  public function getLectureNumber() {
    return $this->lectureNumber;
  }

  public function getQuestionText() {
    return $this->questionText;
  }

  public function getAnswerText() {
    return $this->answerText;
  }

  public function getBroadcast() {
    return $this->broadcast;
  }

  public function getIntentName() {
    return $this->intentName;
  }

}
