import type { postNewQuestionParams } from "@/types/payloads";
import type { postNewQuestionResponse } from "@/types/response";
import axios from "axios";

/**
 * 新規の質問をDBに登録する
 * 質問のインデックス，回答を依頼する学生協力者のユーザIDを返す
 */
const postNewQuestion = async ({
	userId,
	lectureNumber,
	questionText,
}: postNewQuestionParams) => {
	try {
		const { status, data } = await axios.post<postNewQuestionResponse>(
			`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/questions/newQuestion`,
			{
				userId,
				lectureNumber,
				questionText,
			}
		);
		if (status === 201) {
			return data;
		} else {
			throw new Error(
				JSON.stringify({ message: "failed to post new question", data: data })
			);
		}
	} catch (error) {
		throw error;
	}
};

export default postNewQuestion;
