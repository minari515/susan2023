<?php
class LogsController {
  // クラスのコードをここに記述します
}

require_once(dirname(__FILE__) . "/discussions.php");

// クエリパラメータからuserIdを取得
if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    // userIdを基準にデータを取得
    $data = getBotTalkLigsData($userId);

    // HTMLを生成してデータを表示
    echo "<!DOCTYPE html>";
    echo "<html>";
    echo "<head>";
    echo "<title>Logs</title>";
    echo "</head>";
    echo "<body>";
    echo "<h1>Logs</h1>";

    if (empty($data)) {
        echo "データが見つかりません。";
    } else {
      echo "<tr";
      echo ">";
      echo '<table border="1">'; // 枠線を追加
      echo "<tr><th>index</th><th>timestamp</th><th>sender</th><th>message</th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            // // contextNameに応じて背景色を変更
            // if ($row['contextName'] === "question-start") {
            //   echo ' style="background-color: lightblue;"';
            // } elseif ($row['contextName'] === "throw-teacher") {
            //   echo ' style="background-color: lightgreen;"';
            // }
            echo "<td>" . $row['index'] . "</td>";
            echo "<td>" . $row['timestamp'] . "</td>";
            // echo "<td>" . $row['userUid'] . "</td>";
            echo "<td>" . $row['sender'] . "</td>";
            // echo "<td>" . $row['messageType'] . "</td>";
            echo "<td>" . $row['message'] . "</td>";
            // echo "<td>" . $row['contextName'] . "</td>";
            // echo "<td>" . $row['lifespanCount'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "</body>";
    echo "</html>";
} else {
    echo "ユーザーIDが指定されていません。";
}
?>
