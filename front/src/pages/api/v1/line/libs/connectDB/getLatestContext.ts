import type { DialogflowContext, User } from "@/types/models";
import axios, { AxiosError, AxiosResponse } from "axios";

/**
 * DBの対話ログから最新のコンテキストを取得する
 * @param userId 
 * @returns 
 */
const getLatestContexts = async (userId: User["userUid"]) => {
	try {
		const { status, data } = await axios.get<DialogflowContext[]>(
			`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/line/context/${userId}`
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

export default getLatestContexts;
