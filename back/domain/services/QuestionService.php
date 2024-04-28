<?php

require_once(dirname(__FILE__).'../../models/Question.php');
require_once(dirname(__FILE__).'../../../repository/QuestionsRepository.php');

class QuestionService {

  private $questionRepository;

  public function __construct() {
    $this->questionRepository = new QuestionRepository();
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
        $question["intentName"]
      );
    }

    return $questions;
  }
}
