import { Question } from "@/types/models";
import type { FlexMessage } from "@line/bot-sdk";

/**
 * 自動回答のFlexMessageを生成する
 * @param questionIndex 質問のインデックス
 * @param questionText 質問文
 * @param answerText 回答文
 * @param lectureNumber 講義回数
 */
const autoAnswerFlexMessage = (
	questionIndex: number,
	questionText: Question["questionText"],
	answerText: Question["answerText"],
	lectureNumber: Question["lectureNumber"]
): FlexMessage => {
	const lectureNumberDisplay = lectureNumber
		? `第${lectureNumber}回`
		: "未設定";
	return {
		type: "flex",
		altText: `似た質問と回答が見つかりました(index:${questionIndex})`,
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
								type: "box",
								layout: "vertical",
								contents: [
									{
										type: "text",
										text: `${lectureNumberDisplay}`,
										color: "#FFFFFF",
										weight: "bold",
									},
								],
								backgroundColor: "#6366f1",
								position: "absolute",
								paddingTop: "sm",
								paddingBottom: "sm",
								paddingStart: "md",
								paddingEnd: "md",
								cornerRadius: "md",
							},
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
						height: "sm",
					},
					{
						type: "button",
						style: "link",
						action: {
							type: "message",
							label: "ありがとう",
							text: "疑問が解決しました",
						},
						height: "sm",
					},
					{
						type: "button",
						style: "link",
						action: {
							type: "message",
							label: "求めた回答ではない",
							text: "求めた回答ではありません",
						},
						height: "sm",
					},
				],
			},
		},
	};
};

export default autoAnswerFlexMessage;
