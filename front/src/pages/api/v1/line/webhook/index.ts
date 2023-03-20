import { botResponse } from "./types";
import type { DialogflowContext } from "@/types/models";
import type { NextApiRequest, NextApiResponse } from "next";
import type {
	MessageAPIResponseBase,
	WebhookEvent,
} from "@line/bot-sdk/lib/types";
import { SignatureValidationFailed } from "@line/bot-sdk";
import { handleFollow, handleText } from "./handlers";
import { middleware, runMiddleware, replyText, pickContextId } from "../libs";
import { getLatestContexts, postMessageLog } from "../libs/connectDB";

/**
 * @ref https://nextjs.org/docs/api-routes/api-middlewares#custom-config
 */
export const config = {
	api: {
		bodyParser: false,
	},
};

/**
 * LINEボットのWebhookエンドポイント
 * @ref https://developers.line.biz/en/reference/messaging-api/#webhooks
 */
const LineWebhookHandler = async (
	req: NextApiRequest,
	res: NextApiResponse
) => {
	switch (req.method) {
		case "GET":
			// check this api is alive
			res.status(200).json({ message: "active!" });
			break;

		case "POST":
			// Run the middleware
			try {
				await runMiddleware(req, res, middleware);
			} catch (error: unknown) {
				console.error(error);
				if (error instanceof SignatureValidationFailed) {
					res.status(401).end("invalid signature");
					break;
				} else {
					res.status(500).end("something went wrong");
					break;
				}
			}

			// handle webhook body
			const events: WebhookEvent[] = req.body.events;
			try {
				const results = await Promise.all(
					events.map(
						async (event: WebhookEvent) => await webhookEventHandler(event)
					)
				);
				res.status(200).json({
					status: "success",
					results,
				});
			} catch (error: any) {
				console.error(error);
				res.status(500).end(error.message);
			}
			break;

		default:
			res.setHeader("Allow", ["GET", "POST"]);
			res.status(405).end(`Method ${req.method} Not Allowed`);
	}
};

/**
 * 各webhookイベントに対する処理
 */
const webhookEventHandler = async (
	event: WebhookEvent
): Promise<void | MessageAPIResponseBase> => {
	switch (event.type) {
		// LINEボットのトークより何らかのメッセージを受信したとき
		case "message":
			const message = event.message;

			// DBの対話ログからユーザーの最新のコンテキストを取得
			const latestContexts = await getLatestContexts(event.source.userId!)
				.then((contexts: DialogflowContext[]) => {
					return contexts.map((context) => pickContextId(context));
				})
				.catch((error: any) => {
					throw error;
				});

			// ユーザからの受信メッセージを対話ログに保存
			await postMessageLog({
				userId: event.source.userId!,
				messageType: message.type,
				message: message.type == "text" ? message.text : "undefined",
				userType: "student",
				context: latestContexts[0],
			});

			// LINE Botのメッセージ送信結果とDBへ記録するログデータの雛形を準備
			let res: botResponse = {
				messageAPIResponse: undefined,
				messageLog: {
					userId: event.source.userId!,
					messageType: "text",
					message: "message",
					userType: "bot",
					context: null,
				},
			};

			// メッセージタイプに応じて処理をさらに分岐
			switch (message.type) {
				// テキストメッセージを受信したとき
				case "text":
					if (message.text.length > 256) {
						// Dialogflowの入力文字数限界を超えている場合
						res.messageAPIResponse = await replyText(
							event.replyToken,
							`ごめんなさい．メッセージが長すぎます😫．256文字以下にしてください．(${message.text.length}文字でした)`
						);
						res.messageLog.message = `ごめんなさい．メッセージが長すぎます😫．256文字以下にしてください．(${message.text.length}文字でした)`;
					} else {
						// テキストメッセージ処理用のハンドラにイベントを渡し，結果を受け取る
						const _res = await handleText(
							message,
							latestContexts,
							event.replyToken,
							event.source
						);
						res.messageAPIResponse = _res.messageAPIResponse;
						res.messageLog = _res.messageLog;
					}
					break;

				// case "image": // 画像メッセージを受信したとき
				// 	return handleImage(message, event.replyToken);
				// case "video": // 動画メッセージを受信したとき
				// 	return handleVideo(message, event.replyToken);
				// case "audio": // 音声メッセージを受信したとき
				// 	return handleAudio(message, event.replyToken);
				// case "location": // 位置情報メッセージを受信したとき
				// 	return handleLocation(message, event.replyToken);
				// case "sticker": // スタンプメッセージを受信したとき
				// 	return handleSticker(message, event.replyToken);

				default:
					res.messageAPIResponse = await replyText(
						event.replyToken,
						`ごめんなさい．まだその種類のメッセージ(${message.type})には対応できません😫 `
					);
					res.messageLog.message = `ごめんなさい．まだその種類のメッセージ(${message.type})には対応できません😫 `;
			}
			// bot側のメッセージ送信結果をDBに保存
			res.messageAPIResponse && (await postMessageLog(res.messageLog!));
			// botの送信結果を返す
			return res.messageAPIResponse;

		// LINEボットを友だち追加(ブロック解除)したとき
		case "follow":
			return await handleFollow(event.replyToken, event.source);

		// case "unfollow":
		// 	console.log(`Unfollowed this bot: ${JSON.stringify(event)}`);
		// 	break;

		// case "join":
		// 	await replyText(event.replyToken, `Joined ${event.source.type}`);
		// 	break;

		// case "leave":
		// 	console.log(`Left: ${JSON.stringify(event)}`);
		// 	break;

		// case "postback":
		// 	let data = event.postback.data;
		// 	if (data === "DATE" || data === "TIME" || data === "DATETIME") {
		// 		data += `(${JSON.stringify(event.postback.params)})`;
		// 	}
		// 	await replyText(event.replyToken, `Got postback: ${data}`);
		// 	break;

		// case "beacon":
		// 	await replyText(event.replyToken, `Got beacon: ${event.beacon.hwid}`);
		// 	break;

		default:
			if ("replyToken" in event) {
				return await replyText(
					event.replyToken,
					"予期せぬ入力によりエラーが発生しました😫"
				);
			} else {
				throw new Error("unexpected event type");
			}
	}
};

export default LineWebhookHandler;
