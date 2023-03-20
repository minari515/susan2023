import type { FlexMessage } from "@line/bot-sdk";

/**
 * フォローイベント(友だち追加・ブロック解除)のFlexMessage
 * 実験同意を求めるLIFFページへ誘導する
 */
const followFlexMessage: FlexMessage = {
	type: "flex",
	altText: "フォローありがとうございます！",
	contents: {
		type: "bubble",
		size: "giga",
		header: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "text",
					text: "🥳登録ありがとう!",
					size: "xl",
					weight: "bold",
					align: "center",
					color: "#FFFFFF",
					offsetTop: "lg",
				},
			],
			paddingAll: "lg",
			backgroundColor: "#284275",
		},
		body: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "text",
					text: "はじめまして，SUSANbotです！",
					weight: "bold",
					align: "start",
					wrap: true,
					color: "#FFFFFF",
				},
				{
					type: "text",
					text: "あなたの代わりに匿名で先生に質問を送信したり，先生からの回答をお知らせします！",
					align: "start",
					wrap: true,
					color: "#FFFFFF",
				},
				{
					type: "text",
					text: "まずはシステム利用への同意をお願いします！",
					align: "start",
					wrap: true,
					color: "#FFFFFF",
				},
			],
			justifyContent: "center",
			spacing: "xl",
			backgroundColor: "#284275",
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
						type: "uri",
						label: "実験同意ページを開く",
						uri: `https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/`,
					},
				},
			],
			flex: 0,
		},
	},
};

export default followFlexMessage;
