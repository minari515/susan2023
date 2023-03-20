import type { Question } from "@/types/models";
import type { FlexMessage } from "@line/bot-sdk";

/**
 * 回答協力を依頼するFlexMessageを生成する
 * @param questionIndex 質問インデックス
 * @param questionText 質問文
 * @returns 
 */
const assignNewQuestionFlexMessage = (
	questionIndex: Question["index"],
	questionText: Question["questionText"]
): FlexMessage => {
	return {
		type: "flex",
		altText: "回答にご協力お願いします！",
		contents: {
			type: "bubble",
			header: {
				type: "box",
				layout: "vertical",
				contents: [
					{
						type: "text",
						text: "🕊 回答お願いします！",
						size: "xl",
						weight: "bold",
						align: "center",
						color: "#FFFFFF",
					},
				],
				backgroundColor: "#284275",
				paddingAll: "lg",
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
								paddingAll: "lg",
							},
						],
						backgroundColor: "#284275",
					},
					{
						type: "box",
						layout: "vertical",
						contents: [
							{
								type: "text",
								text: "新しい質問が投稿されました.",
								align: "start",
								wrap: true,
							},
							{
								type: "text",
								wrap: true,
								weight: "bold",
								text: "回答できそうな質問であれば，\nぜひご協力お願いします",
							},
						],
						alignItems: "center",
					},
				],
				spacing: "md",
				paddingAll: "none",
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
							label: "質問の詳細へ",
							uri: `https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/question/${questionIndex}`,
							altUri: {
								desktop: `https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/question/${questionIndex}`,
							},
						},
					},
				],
				flex: 0,
			},
		},
	};
};

export default assignNewQuestionFlexMessage;
