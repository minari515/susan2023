<?php

require_once(dirname(__FILE__).'../../domain/services/QuestionService.php');
require_once(dirname(__FILE__).'../../domain/exceptions/NotFoundException.php');

class QuestionsAppService {

  private $questionService;
  
  public function __construct() {
    $this->questionService = new QuestionService();
  }

  /**
    * 指定のインデックスを起点に最新30件の質疑応答情報を取得する
    * @param int $index 質疑応答情報のインデックス
    * @return QuestionEntity[] 質問データ
    */
  public function getQuestionsFrom($index) {
    return (array)$this->questionService->getQuestions(30, $index);
  }

  /**
   * 指定のインデックスの質疑応答情報を取得する
   * @param int $index 質疑応答情報のインデックス
   */
  public function getSelectedQuestion($index) {
    $result = $this->questionService->getSelectedQuestionData($index);
    if(is_null($result)){
      throw new NotFoundException("指定された質問が見つかりませんでした", ["selectIndex" => $index]);
    }else{
      return $result;
    }
  }

  /**
   * 最新質問5件を取得(チャットボットの「みんなの質問を見せて」返答用)
   * @return array 質問データ
   */
  public function getLatestQuestions(){
    return (array)$this->questionService->getQuestions(5);
  }
}
