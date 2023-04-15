#!/bin/sh

curl -v -X POST https://api-data.line.me/v2/bot/richmenu/richmenu-33b5f6695eda925b2bfe69ff9ed3d196/content \
-H "Authorization: Bearer {gLyuBroDre7mfrzJhK+VwvoPkIuKfiCHnIPNPecVkxT7QcuxliFs8xjiT30uqsFOyw+m7PR4T2PVTr/+/EpE+IoumR7eFW3BgYIdc5kCVgkXfhOZlFVnQ+ju8lavt7eVKWASxMFSxogtbXf0/YU0kwdB04t89/1O/w1cDnyilFU=}" \
-H "Content-Type: image/png" \
-T RichMenu.png