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
  console.log(apiKey)
  if (apiKey) {
    console.log("true");
  } else {
    console.log("false");
  }
  // const configuration = new Configuration({
  //   apiKey: apiKey,
  // });
  // const openai = new OpenAIApi(configuration);
  // console.log(openai);
  // try {
  //   const response = await openai.createChatCompletion({
  //     model: "gpt-3.5-turbo",
  //     messages: [{ role: "assistant", content: message.text }],
  //   });
  //   console.log(response);

  //   if (response.data.choices && response.data.choices.length > 0) {
  //     if (response.data.choices[0].message) {
  //       const res = response.data.choices[0].message.content;
  //       replyMessage = [
  //         {
  //           type: "text",
  //           text: res,
  //         } as TextMessage,
  //       ];
  //       console.log(replyMessage);
  //     }
  //   }
  // } catch (error) {
  //   console.error("GPTのリクエストエラー:", error);
  // }

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
