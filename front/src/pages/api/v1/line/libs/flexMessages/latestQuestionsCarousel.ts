import type { Question } from "@/types/models";
import type {
	FlexBubble,
	FlexCarousel,
	FlexMessage,
} from "@line/bot-sdk/lib/types";

/**
 * 最新質問をカルーセルで表示するFlexMessageを生成する
 * @param questions 最大5件の質問
 */
const latestQuestionsCarousel = (questions: Question[]): FlexMessage => {
	return {
		type: "flex",
		altText: "最新の質問を表示します",
		contents: {
			type: "carousel",
			contents: questions.map((question) => {
				return {
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
												text: question.lectureNumber
													? `第${question.lectureNumber}回`
													: "未設定",
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
						],
						backgroundColor: "#284275",
					},
					body: {
						type: "box",
						layout: "vertical",
						contents: [
							{
								type: "text",
								text: `${question.questionText}`,
								size: "lg",
								weight: "bold",
								wrap: true,
								color: "#FFFFFF",
								align: "start",
							},
						],
						backgroundColor: "#284275",
						justifyContent: "center",
						paddingAll: "xl",
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
									uri: `https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/question/${question.index}`,
								},
								height: "sm",
							},
						],
						height: "60px",
						maxHeight: "60px",
					},
				} as FlexBubble;
			}),
		} as FlexCarousel,
	};
};

export default latestQuestionsCarousel;
