#!/bin/sh

#本番
# curl -v -X POST https://api.line.me/v2/bot/richmenu \
#テスト
curl -v -X POST https://api.line.me/v2/bot/richmenu/validate \
-H 'Authorization: Bearer {CHANNEL_ACCESS_TOKEN}' \
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
              "x": 0,
              "y": 0,
              "width": 1667,
              "height": 845
          },
      },
      {
          "bounds": {
              "x": 1667,
              "y": 0,
              "width": 833,
              "height": 845
          },
          "action": {
              "type": "uri",
              "uri": "https://liff.line.me/${NEXT_PUBLIC_LIFF_ID}/howToUse"
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
              "uri": "https://liff.line.me/${NEXT_PUBLIC_LIFF_ID}/questionsList"
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