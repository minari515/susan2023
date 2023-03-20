import { DialogflowIntent, Question } from "@/types/models";
import { PostDialogflowIntentPayload } from "@/types/payloads";
import axios, { AxiosError, AxiosResponse } from "axios";
import { useEffect, useState } from "react";

/**
 * Dialogflowのインテントを管理するためのhooks
 * @param questionText 質問文
 * @param existedIntentName 既存インテントのintentName
 **/
const useDialogflowIntent = (
	questionText: Question["questionText"],
	existedIntentName?: DialogflowIntent["intentName"]
) => {
	const [intent, setIntent] = useState<DialogflowIntent>({
		trainingPhrases: [questionText],
	} as DialogflowIntent);

	useEffect(() => {
		!!existedIntentName && getIntentData(existedIntentName);
	}, []);

	/**
	 * DialogflowのIntentに登録されているトレーニングフレーズを取得する
	 * @param intentName
	 * @return intent
	 */
	const getIntentData = async (intentName: string) => {
		try {
			const { status, data } = await axios.get<DialogflowIntent>(
				`/api/v1/dialogflow/intents?intentName=${intentName}`
			);
			if (status === 200) {
				setIntent(data);
			} else {
				throw new Error("Intentの取得に失敗しました");
			}
		} catch (error: any) {
			alert("サーバーでエラーが発生しました．");
			throw new Error(error.message);
		}
	};

	/**
	 * DialogflowのIntentを更新する
	 * @param question 質問データ
	 */
	const postIntent = async (question: Question) => {
		try {
			const { status, data } = await axios.post<DialogflowIntent>(
				`/api/v1/dialogflow/intents${
					intent?.intentName ? "?intentName=" + intent.intentName : ""
				}`,
				{
					questionIndex: question.index,
					lectureNumber: question.lectureNumber,
					intentName: intent?.intentName,
					trainingPhrases: intent?.trainingPhrases,
					responseText: intent!.responseText,
				} as PostDialogflowIntentPayload
			);
			if (status !== 201) throw new Error("Intentの更新に失敗しました");
			setIntent(data);
			return data;
		} catch (error: any) {
			console.error(error);
			if (error instanceof AxiosError) {
				throw new AxiosError(`update intent: ${error.message}`);
			} else {
				throw new Error(
					`update intent: ${error.message || "不明なエラーが発生しました"} `
				);
			}
		}
	};

	return {
		intent,
		setIntent,
		getIntentData,
		postIntent,
	};
};

export default useDialogflowIntent;
