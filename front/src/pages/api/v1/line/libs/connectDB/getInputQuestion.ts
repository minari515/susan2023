import { Question, User } from "@/types/models";
import axios, { AxiosError, AxiosResponse } from "axios";

/**
 * チャットボットに送信した質問文をDBの対話ログから取得する
 * @param userId
 */
const getInputQuestion = async (userId: User["userUid"]) => {
	try {
		const { status, data } = await axios.get<Question["questionText"]>(
			`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/line/question/${userId}`
		);
		if (status === 200) {
			return data;
		} else {
			throw new Error(`status code is ${status}`);
		}
	} catch (error) {
		if (error instanceof AxiosError) {
			throw new Error(error.message);
		} else {
			throw new Error("原因不明のエラーが発生しました．");
		}
	}
};

export default getInputQuestion;
