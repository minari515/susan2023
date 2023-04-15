#!/bin/sh

#テスト
#curl -v -X POST https://api.line.me/v2/bot/richmenu/validate \

#本番
curl -v -X POST https://api.line.me/v2/bot/richmenu \
-H 'Authorization: Bearer {gLyuBroDre7mfrzJhK+VwvoPkIuKfiCHnIPNPecVkxT7QcuxliFs8xjiT30uqsFOyw+m7PR4T2PVTr/+/EpE+IoumR7eFW3BgYIdc5kCVgkXfhOZlFVnQ+ju8lavt7eVKWASxMFSxogtbXf0/YU0kwdB04t89/1O/w1cDnyilFU=}' \
-H 'Content-Type: application/json' \
-d \
'{
  "size":{
      "width":2500,
      "height":1686
  },
  "selected": true,
  "name": "SUSAN Rich Menu",
  "chatBarText": "メニューを開く",
  "areas": [
      {
          "bounds": {
              "x": 1667,
              "y": 0,
              "width": 833,
              "height": 845
          },
          "action": {
              "type": "uri",
              "uri": "https://liff.line.me/1660801710-YN7xXL0B/howToUse"
          }
      },
      {
          "bounds": {
              "x": 0,
              "y": 845,
              "width": 833,
              "height": 843
          },
          "action": {
              "type": "message",
              "text": "質問があります"
          }
      },
      {
          "bounds": {
              "x": 833,
              "y": 845,
              "width": 834,
              "height": 843
          },
          "action": {
              "type": "uri",
              "uri": "https://liff.line.me/1660801710-YN7xXL0B/questionsList"
          }
      },
      {
          "bounds": {
              "x": 1667,
              "y": 845,
              "width": 833,
              "height": 843
          },
          "action": {
              "type": "uri",
              "uri": "https://moodle2023.wakayama-u.ac.jp/"
          }
      }
  ]
}'