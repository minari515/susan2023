import type { Question, User } from "@/types/models";
import { lineClient } from "../../libs";
import { assignNewQuestionFlexMessage } from "../../libs/flexMessages";
/**
 * 新しい質問投稿を通知する
 */
const notifyNewQuestion = async ({
	userIds,
	questionIndex,
	questionText
}: {
	userIds: User["userUid"][];
	questionIndex: Question["index"];
	questionText: Question["questionText"];
}) => {
	// 質問に対する回答を送信

	const message = assignNewQuestionFlexMessage(questionIndex, questionText);

	try {
		return await lineClient.multicast(userIds, message);
	} catch (error) {
		throw error;
	}
};

export default notifyNewQuestion;
