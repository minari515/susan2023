import { Question } from "@/types/models";
import type { FlexMessage } from "@line/bot-sdk";

/**
 * 質問と回答を表示するFlexMessageを生成する
 * @param questionIndex 質問インデックス
 * @param questionText 質問文
 * @param answerText 回答文
 */
const displayQandA = (
	questionIndex: number,
	questionText: Question["questionText"],
	answerText: Question["answerText"]
): FlexMessage => ({
	type: "flex",
	altText: `${answerText}`,
	contents: {
		type: "bubble",
		header: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "box",
					layout: "vertical",
					contents: [
						{
							type: "text",
							text: "🤔質問",
							size: "lg",
							weight: "bold",
							align: "center",
							color: "#FFFFFF",
						},
						{
							type: "text",
							text: "Question",
							size: "xxs",
							weight: "bold",
							align: "center",
							color: "#FFFFFF",
						},
					],
				},
				{
					type: "box",
					layout: "vertical",
					contents: [
						{
							type: "text",
							text: `${questionText}`,
							size: "lg",
							weight: "bold",
							wrap: true,
							color: "#FFFFFF",
							align: "start",
						},
					],
					paddingTop: "md",
				},
			],
			backgroundColor: "#284275",
		},
		body: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "box",
					layout: "vertical",
					contents: [
						{
							type: "text",
							text: "💡回答",
							size: "lg",
							weight: "bold",
							align: "center",
						},
						{
							type: "text",
							text: "Answer",
							size: "xxs",
							weight: "bold",
							align: "center",
						},
					],
				},
				{
					type: "box",
					layout: "vertical",
					contents: [
						{
							type: "text",
							text: `${answerText}`,
							size: "lg",
							weight: "bold",
							wrap: true,
						},
					],
					paddingTop: "md",
				},
			],
			backgroundColor: "#FFFFFF",
		},
		footer: {
			type: "box",
			layout: "vertical",
			contents: [
				{
					type: "button",
					action: {
						type: "uri",
						label: "詳細へ",
						uri: `https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/question/${questionIndex}`,
					},
				},
			],
		},
	},
});

export default displayQandA;
