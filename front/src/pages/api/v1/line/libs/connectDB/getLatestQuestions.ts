import { Question } from "@/types/models";
import axios, { AxiosError } from "axios";

/**
 * 「みんなの質問を見せて」用の質問を取得する
 * @returns 最新質問5件
 */
const getLatestQuestions = async () => {
	try {
		const { status, data } = await axios.get<Question[]>(
			`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/questions/latest`
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

export default getLatestQuestions;
