import { DialogflowContext } from "@/types/models";
import { v2 } from "@google-cloud/dialogflow";
import clientConfig from "../libs/clientConfig";
import { Configuration, OpenAIApi } from "openai";
// import { TextMessage } from "@line/bot-sdk";
import {
  Message,
  TextEventMessage,
  TextMessage,
  EventSource,
  StickerMessage,
} from "@line/bot-sdk/lib/types";

const sessionsClient = new v2.SessionsClient(clientConfig);
const languageCode = "ja";

// (async () => {
//   const completion = await openai.createChatCompletion({
//     model: "gpt-3.5-turbo",
//     messages: [{ role: "user", content: "ChatGPT について教えて" }],
//   });
//   console.log(completion.data.choices[0].message);
// })();

/**
 * @param uniqueId sessionIDとして使用する一意のID(expected: LINE Message ID)
 * @param inputText ユーザーの発話
 * @param contexts ユーザーの最新のコンテキスト
 * @returns Dialogflowからのレスポンス(NLP解析結果)
 */
export const detectIntentgpt = async (
  uniqueId: string,
  inputText: string,
  contexts: DialogflowContext[],
  replyMessage: Message[]
) => {
  const sessionId = uniqueId;
  //dialogflowのキー
  const sessionPath = sessionsClient.projectAgentSessionPath(
    process.env.DIALOGFLOW_PROJECT_ID!,
    sessionId
  );

  //gptのキー
  const configuration = new Configuration({
    apiKey: process.env.OPENAI_API_KEY,
  });
  const openai = new OpenAIApi(configuration);
  const completion = await openai.createChatCompletion({
    model: "gpt-3.5-turbo",
    messages: [{ role: "user", content: inputText}],
  });

  const res = completion.data.choices[0].message.content;

  return replyMessage = [
    {
      type: 'text',
      text:  res
    } as Message,
  ];

  const inputContexts = contexts.reduce((acc, cur) => {
    if (!cur.name || cur.name == "__system_counters__") return acc;
    cur.name = sessionPath + "/contexts/" + cur.name;
    acc.push(cur);
    return acc;
  }, [] as DialogflowContext[]);

  const request = {
    session: sessionPath,
    queryInput: {
      text: {
        text: inputText,
        languageCode: languageCode,
      },
    },
    queryParams: {
      contexts: inputContexts.length > 0 ? inputContexts : null,
    },
  };
  const [response] = await sessionsClient.detectIntent(request);
  return response;
};
