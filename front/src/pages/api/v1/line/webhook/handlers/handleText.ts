import type { DialogflowContext } from "@/types/models";
import type { postMessageLogParams } from "@/types/payloads";
import {
  Message,
  TextEventMessage,
  TextMessage,
  EventSource,
  StickerMessage,
} from "@line/bot-sdk/lib/types";
import { lineClient } from "@/pages/api/v1/line/libs";
import { detectIntent } from "@/pages/api/v1/dialogflow/sessions/detectIntent";
import {
  autoAnswerFlexMessage,
  checkInputNewQuestion,
  completeSendNewQuestion,
  latestQuestionsCarousel,
  offerSendNewMessage,
} from "../../libs/flexMessages";
import calcLectureNumber from "@/utils/calcLectureNumber";
import {
  getInputQuestion,
  getLatestQuestions,
  postNewQuestion,
} from "../../libs/connectDB";
import notifyNewQuestion from "../../push/handlers/notifyNewQuestion";
import { Configuration, OpenAIApi } from "openai";

/**
 * LINE botのテキストメッセージを受け取ったときの処理
 */
const handleText = async (
  message: TextEventMessage,
  contexts: DialogflowContext[],
  replyToken: string,
  source: EventSource
) => {
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

  // const configuration = new Configuration({
  //   apiKey: process.env.OPENAI_API_KEY,
  // });
  // const openai = new OpenAIApi(configuration);

  // (async () => {
  //   const completion = await openai.createChatCompletion({
  //     model: "gpt-3.5-turbo",
  //     messages: [{ role: "user", content: "ChatGPT について教えて" }],
  //   });
  //   console.log(completion.data.choices[0].message);
  // })();
  // gpt-3.5-turboで解答を生成
  // const url = 'https://api.line.me/v2/bot/message/reply';
  // const prompt = message.text;
  // const requestOptions = {
  //   "method": "post",
  //   "headers": {
  //     "Content-Type": "application/json",
  //     "Authorization": "Bearer "+ process.env.OPENAI_APIKEY
  //   },
  //   "payload": JSON.stringify({
  //     "model": "gpt-3.5-turbo",
  //     "messages": [
  //       {"role": "user", "content": prompt}]
  //   })
  // }
  // const response = UrlFetchApp.fetch("https://api.openai.com/v1/chat/completions", requestOptions);

  // const responseText = response.getContentText();
  // const json = JSON.parse(responseText);
  // const text = json['choices'][0]['message']['content'].trim();
  // UrlFetchApp.fetch(url, {
  //   'headers': {
  //     'Content-Type': 'application/json; charset=UTF-8',
  //     'Authorization': 'Bearer ' + process.env.LINE_ACCESS_TOKEN,
  //   },
  //   'method': 'post',
  //   'payload': JSON.stringify({
  //     'replyToken': replyToken,
  //     'messages': [{
  //       'type': 'text',
  //       'text': text,
  //     }]
  //   })
  // });

  // Dialogflowにテキストを送信・解析結果から応答を生成する
  /**
   * Dialogflowの解析結果
   */
  const nlpResult = await detectIntent(message.id, message.text, contexts);
  if (!nlpResult.queryResult) throw new Error("queryResultが存在しません");

  switch (nlpResult.queryResult.action) {
    case "QuestionStart": // input:「質問があります」
    case "AskTheTeacherDirectly": // input:「(質問を)書き直す」
      // 質問文の入力を促すメッセージを返す
      const { type, number } = calcLectureNumber(new Date());
      replyMessage = [
        {
          type: "text",
          text:
            type && number
              ? `データサイエンス入門${type}第${number}回講義の質問を受付中です！256字未満で具体的に書いてもらえる？😊`
              : "質問を200字未満で具体的に書いてもらえる？😊",
        } as TextMessage,
      ];
      break;

    case "AnswerToTheQuestion": // 自動回答
      let autoAnswerParameters = nlpResult.queryResult.parameters!.fields!; // DialogflowのIntentに設定されたパラメータ
      replyMessage = [
        autoAnswerFlexMessage(
          Number(autoAnswerParameters["questionIndex"].stringValue!),
          autoAnswerParameters["originQuestion"].stringValue!,
          nlpResult.queryResult.fulfillmentText!,
          Number(autoAnswerParameters["lectureNumber"].stringValue!)
        ),
      ];
      break;

    case "NotFoundAnswer": // 類似質問が見つからなかったとき
    case "RequestDifferentAnswer": // input: 「求めた回答ではありません」
      // 質問文の確認・送信または修正を促すメッセージを返す
      replyMessage = [
        offerSendNewMessage(await getInputQuestion(source.userId!)),
      ];
      break;

    case "FreeWriting": // input: 「(質問を)書き直す」
      // 書き直した質問文の確認・送信または修正を促すメッセージを返す
      replyMessage = [
        checkInputNewQuestion(await getInputQuestion(source.userId!)),
      ];
      break;

    case "CompleteWritingQuestion": // input:「質問を送信する」
      // 質問文の送信完了を伝えるメッセージを返す
      // 質問文をDBに登録する
      let postNewQuestionText = await getInputQuestion(source.userId!);
      const { questionIndex, discussionIndex, assignedStudents } =
        await postNewQuestion({
          userId: source.userId!,
          lectureNumber: calcLectureNumber(new Date()).number,
          questionText: postNewQuestionText,
        });
      // 質問文の送信完了を伝えるメッセージ
      replyMessage = [completeSendNewQuestion(questionIndex)];
      nlpResult.queryResult.outputContexts = null; // 質問送信後はcontextを削除する
      // 回答協力者に通知を送信する
      assignedStudents.length > 0 &&
        notifyNewQuestion({
          userIds: assignedStudents,
          questionIndex,
          questionText: postNewQuestionText,
        });
      break;

    case "ShowOthersQuestions": // input:「みんなの質問を見せて」
      replyMessage = [latestQuestionsCarousel(await getLatestQuestions())];
      break;

    case "cancel":
      // 処理をキャンセル・コンテキストをリセット
      replyMessage = [
        {
          type: "text",
          text: "また質問してね！",
        } as TextMessage,
        {
          type: "sticker",
          packageId: "11539",
          stickerId: "52114128",
        } as StickerMessage,
      ];
      nlpResult.queryResult.outputContexts = null; // contextを削除する
      break;

    default:
      // 原則DialogflowのIntentに設定しているResponseを返す，設定されていない場合は不明なメッセージとして返答するふぃｘ
      replyMessage = nlpResult.queryResult.fulfillmentText
        ? [
            {
              type: "text",
              text: `${nlpResult.queryResult.fulfillmentText}`,
            } as TextMessage,
          ]
        : [
            {
              type: "text",
              text: `すみません，よくわかりませんでした🤔`,
            } as TextMessage,
            {
              type: "sticker",
              packageId: "11539",
              stickerId: "52114129",
            } as StickerMessage,
          ];
  }

  /**
   * LINE botから返信した結果
   */
  const messageAPIResponse = await lineClient.replyMessage(
    replyToken,
    replyMessage
  );
  /**
   * DBに登録する対話ログの内容
   */
  const messageLog: postMessageLogParams = {
    userId: source.userId!,
    userType: "bot",
    messageType: replyMessage[0].type,
    message:
      ("text" in replyMessage[0] && replyMessage[0].text) ||
      ("altText" in replyMessage[0] && replyMessage[0].altText) ||
      "unknown message",
    context: nlpResult.queryResult.outputContexts
      ? (nlpResult.queryResult.outputContexts[0] as DialogflowContext)
      : null,
  };

  return { messageAPIResponse, messageLog };
};

export default handleText;
