<?php

require_once(dirname(__FILE__).'../../domain/services/QuestionService.php');
require_once(dirname(__FILE__).'../../domain/services/UserService.php');
require_once(dirname(__FILE__)."../../../../repository/LogRepository.php");
require_once(dirname(__FILE__).'../../domain/exceptions/NotFoundException.php');

class QuestionsAppService {

  private $questionService;
  private $userService;
  private $logRepository;
  
  public function __construct() {
    $this->questionService = new QuestionService();
    $this->userService = new UserService();
    $this->logRepository = new LogRepository();
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

  /**
   * 質問投稿
   * @param string $userId ユーザID
   * @param int $lectureNumber 講義番号
   * @param string $questionText 質問内容
   * @return array 質問インデックスと回答協力者
   */
  public function postQuestion($userId, $lectureNumber, $questionText) {

    // 質問を登録
    $question = $this->questionService->addNewQuestion($userId, $lectureNumber, $questionText);

    // 回答協力者を割り振る
    $assignableUsers = $this->userService->getAssignableUsers($userId);
    $assignedIndex = $this->userService->assignAnswerers($assignableUsers, $question->index);

    return [
      "questionIndex" => $question->index, 
      "assignedStudents" => $assignableUsers
    ];
  }

  /**
   * 質問閲覧時の記録とユーザと質問の関連付け
   * @param int $questionIndex 質問のインデックス
   * @param string $userId ユーザID
   */
  public function recordQuestionView($questionIndex, $userId) {
    // ページビュー履歴を保存
    $savedLogIndex = $this->logRepository->savePageViewHistory($userId, $questionIndex);

    // 閲覧した質問がユーザ自身が投稿した質問であるか確認
    $isQuestionByUser = $this->questionService->isQuestionByUser($questionIndex, $userId);

    // 閲覧した質問がユーザに回答協力を求めている質問であるか確認
    $isAssignedQuestion = $isQuestionByUser 
      ? false // 質問者自身に回答協力を求めていない
      : $this->questionService->isAssignedQuestion($questionIndex, $userId);

    return [
      "logIndex" => $savedLogIndex,
      "isYourQuestion" => $isQuestionByUser,
      "isAssigner" => $isAssignedQuestion
    ];
  }

  /**
   * 質問者か確認
   * @param int $questionIndex 質問のインデックス
   * @param string $userId ユーザID
   */
  public function checkIsYourQuestion($questionIndex, $userId) {
    $isQuestionByUser = $this->questionService->isQuestionByUser($questionIndex, $userId);
    return ["isQuestioner" => $isQuestionByUser];
  }
}
