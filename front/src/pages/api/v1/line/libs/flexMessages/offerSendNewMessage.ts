import { Question } from "@/types/models";
import type { FlexMessage } from "@line/bot-sdk";

/**
 * 新規の質問として投稿することを促すFlexMessageを生成する
 * @param inputQuestionText 質問文
 */
const offerSendNewMessage = (
	inputQuestionText: Question["questionText"]
): FlexMessage => ({
	type: "flex",
	altText: "まだ誰もしていない質問を送ってみよう！",
	contents: {
		type: "bubble",
		header: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "text",
					text: "🙋🏻‍♂️先生に質問してみよう！",
					weight: "bold",
					size: "lg",
					color: "#FFFFFF",
				},
				{
					type: "text",
					text: "まだ誰もしていない質問です🥳",
					color: "#FFFFFF",
					wrap: true,
				},
				{
					type: "text",
					text: "匿名で送信して先生に答えてもらいましょう！",
					color: "#FFFFFF",
					wrap: true,
				},
			],
			backgroundColor: "#284275",
		},
		body: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "text",
					text: "入力された質問文",
					size: "xs",
					align: "center",
					color: "#B4B4B4",
					offsetBottom: "md",
				},
				{
					type: "text",
					text: `${inputQuestionText}`,
					wrap: true,
				},
			],
		},
		footer: {
			type: "box",
			layout: "vertical",
			spacing: "sm",
			contents: [
				{
					type: "button",
					style: "link",
					height: "sm",
					action: {
						type: "message",
						label: "このまま先生に送る",
						text: "質問を送信",
					},
				},
				{
					type: "button",
					style: "link",
					height: "sm",
					action: {
						type: "message",
						label: "書き直す",
						text: "書き直す",
					},
				},
				{
					type: "button",
					style: "link",
					height: "sm",
					action: {
						type: "message",
						label: "質問しない",
						text: "キャンセル",
					},
				},
			],
			flex: 0,
		},
	},
});

export default offerSendNewMessage;
