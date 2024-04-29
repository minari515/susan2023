<?php

require_once(dirname(__FILE__).'../../models/Question.php');
require_once(dirname(__FILE__).'../../../repository/QuestionsRepository.php');
require_once(dirname(__FILE__).'../../../repository/AssignmentsRepository.php');

class QuestionService {

  private $questionRepository;
  private $assignmentsRepository;

  public function __construct() {
    $this->questionRepository = new QuestionRepository();
    $this->assignmentsRepository = new AssignmentsRepository();
  }

  /**
   * 指定のインデックスを起点に質疑応答情報を取得する
   * @param int $range 取得する質問の数
   * @param int $startIndex 取得の起点となる質問のインデックス
   * @return QuestionEntity[] 質問データの配列
   */
  public function getQuestions($range = 30, $startIndex = 99999) {
    $questions = [];
    
    $data = $this->questionRepository->findQuestions($range, $startIndex);
    
    foreach($data as $key => $question){
      $questions[] = new QuestionEntity(
        $question["index"],
        $question["timestamp"],
        $question["lectureNumber"],
        $question["questionText"],
        $question["answerText"],
        $question["broadcast"],
        $question["intentName"],
        null,
        null
      );
    }

    return $questions;
  }

  /**
   * 指定のインデックスの質疑応答情報を取得する
   * @param int $index 質疑応答情報のインデックス
   */
  public function getSelectedQuestionData($index) {
    $data = $this->questionRepository->findQuestion($index);
    
    if(empty($data)){
      return null;
    }

    $question = new QuestionEntity(
      $data["index"],
      $data["timestamp"],
      $data["lectureNumber"],
      $data["questionText"],
      $data["answerText"],
      $data["broadcast"],
      $data["intentName"],
      null,
      null
    );

    return $question;
  }

  /**
   * 新規質問投稿
   * @param string $userId ユーザID
   * @param int $lectureNumber 講義番号
   * @param string $questionText 質問内容
   */
  public function addNewQuestion($userId, $lectureNumber, $questionText) {
    $savedQuestionIndex = $this->questionRepository->saveNewQuestion($userId, $lectureNumber, $questionText);
    $savedQuestion = new QuestionEntity(
      $savedQuestionIndex,
      date("Y-m-d H:i:s"),
      $lectureNumber,
      $questionText,
      null,
      0,
      null,
      null,
      null
    );
    return $savedQuestion;
  }

  /**
   * 指定の質問がユーザ自身が投稿した質問であるか確認
   * @param int $questionIndex 質問のインデックス
   * @param string $userId ユーザID
   */
  public function isQuestionByUser($questionIndex, $userId) {
    return $this->questionRepository->isQuestionByUser($questionIndex, $userId);
  }

  /**
   * 指定の質問がユーザに回答協力を求めている質問であるか確認
   * @param int $questionIndex 質問のインデックス
   * @param string $userId ユーザID
   */
  public function isAssignedQuestion($questionIndex, $userId) {
    return $this->assignmentsRepository->isAssigned($questionIndex, $userId);
  }
}
