import { User } from "@/types/models";
import axios, { AxiosError } from "axios";
import { useEffect, useState } from "react";

/**
 * アンケートの管理を行うカスタムフック
 * @returns
 */
const useQuestionnaire = () => {
	// アンケートの回答状況
	const [isQuestionnaireCompleted, setIsQuestionnaireCompleted] =
		useState(false);

	/**
	 * アンケートの回答状況(回答済みか)を確認する
	 * @param userIdToken LIFFのユーザIDトークン
	 */
	const checkIsQuestionnaireCompleted = async (userIdToken: User["token"]) => {
		try {
			const { status, data } = await axios.get<{
				isQuestionnaireCompleted: boolean;
			}>(
				`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/questionnaires?userIdToken=${userIdToken}`
			);
			if (status === 200) {
				setIsQuestionnaireCompleted(data.isQuestionnaireCompleted);
			} else {
				throw new Error("failed to check questionnaire");
			}
		} catch (error: any) {
			if (error instanceof AxiosError) {
				const { status, data } = error.response!;
			}
			throw error;
		}
	};

	/**
	 * アンケートの回答を送信する
	 * @param userIdToken
	 * @param payload アンケートの回答
	 */
	const postQuestionnaire = async (userIdToken: User["token"], payload: {}) => {
		try {
			const { status, data } = await axios.post(
				`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/questionnaires`,
				{
					userIdToken,
					questionnaire: payload,
				}
			);
			if (status === 201) {
				setIsQuestionnaireCompleted(true);
			} else {
				throw new Error("failed to post questionnaire");
			}
		} catch (error: any) {
			if (error instanceof AxiosError) {
				const { status, data } = error.response!;
			}
			throw error;
		}
	};

	return {
		isQuestionnaireCompleted,
		checkIsQuestionnaireCompleted,
		postQuestionnaire,
	};
};

export default useQuestionnaire;
