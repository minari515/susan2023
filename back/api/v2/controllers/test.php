<?php
ini_set('display_errors', 1);

/**
 * LINE�̃��[�U���E�Θb���O�̃R���g���[���[
 */
class TestController
{
  public $code = 200;
  public $url;
  public $request_body;

  // function __construct()
  // {
  //   $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . mb_substr($_SERVER['SCRIPT_NAME'], 0, -9) . basename(__FILE__, ".php") . "/";
  //   $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'), "UTF8", "ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"), true);
  // }

  /**************************************************************************** */
  /**
   * GET���\�b�h
   * @param array $args
   * @return array ���X�|���X
   */
  public function get($args)
  {
    $this->code = 200;
    return ["good" => "yeah"];
  }
}
