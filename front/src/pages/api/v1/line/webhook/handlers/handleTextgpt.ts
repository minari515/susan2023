import {
  Message,
  TextEventMessage,
  TextMessage,
} from "@line/bot-sdk/lib/types";
import { lineClient } from "@/pages/api/v1/line/libs";
import { Configuration, OpenAIApi } from "openai";

/**
 * LINE botのテキストメッセージを受け取ったときの処理
 */
const handleTextgpt = async (message: TextEventMessage, replyToken: string) => {
  /**
   * LINE botから返信するメッセージ配列
   */
  let replyMessage: Message[] = [
    {
      type: "text",
      text: "すみません，よくわかりませんでした🤔",
    },
  ];
  console.log(replyMessage);

  /**
   * gptによる回答生成
   */
  const apiKey = process.env.OPENAI_API_KEY;
  if (apiKey) {
    console.log("true");
  } else {
    console.log("false");
  }
  const configuration = new Configuration({
    apiKey: apiKey,
  });
  const openai = new OpenAIApi(configuration);
  console.log(openai);
  try {
    const response = await openai.createChatCompletion({
      model: "gpt-3.5-turbo",
      messages: [
        { role:"system", content:
          "あなたは送られてきた質問をカテゴリ別に分類する人です.\
          送られる質問に対し，カテゴリ名のみを返してください．\
          \
          制約条件：\
          ＊返信する内容はカテゴリ名のみで行ってください\
          ＊chatbotの自身を示す一人称は，私です\
          \
          カテゴリ名：\
          ＊chatbotシステムに関する質問\
          ＊授業に関する質問\
          ＊課題に関する質問\
          ＊エラーに関する質問\
          ＊データの前処理に関する質問\
          ＊プログラム自体に関する質問\
          \
          回答の例：\
          ＊chatbotシステムに関する質問\
          ＊授業に関する質問\
          ＊課題に関する質問\
          ＊エラーに関する質問\
          ＊データの前処理に関する質問\
          ＊プログラム自体に関する質問\
          "
          },
        { role: "assistant", content: message.text },
        { role: "user", content:"この質問内容に相当するカテゴリを返してください" }
      ],
    });
    console.log(response);

    if (response.data.choices && response.data.choices.length > 0) {
      if (response.data.choices[0].message) {
        const res = response.data.choices[0].message.content;
        replyMessage = [
          {
            type: "text",
            text: res,
          } as TextMessage,
        ];
        console.log(replyMessage);
      }
    }
  } catch (error) {
    console.error("GPTのリクエストエラー:", error);
  }

  /**
   * LINE botから返信した結果
   */
  const messageAPIResponse = await lineClient.replyMessage(
    replyToken,
    replyMessage
  );

  return { messageAPIResponse };
};

export default handleTextgpt;
