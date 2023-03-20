import type { EventSource } from "@line/bot-sdk";
import { lineClient } from "@/pages/api/v1/line/libs";
import { followFlexMessage } from "@/pages/api/v1/line/libs/flexMessages";

/**
 * フォローイベント(友だち追加・ブロック解除)を処理する
 */
const handleFollow = async (replyToken: string, source: EventSource) => {
	// 実験同意依頼のFlexMessageを送信
	await lineClient.replyMessage(replyToken, followFlexMessage);
};

export default handleFollow;
