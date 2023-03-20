import { Question } from "@/types/models";
import type { PushLineMessagePayload } from "@/types/payloads";
import axios, { AxiosError, AxiosResponse } from "axios";
import { useEffect } from "react";

/** LINEボットに関する処理のhooks */
const useLineMessages = (
	eventType: PushLineMessagePayload["event"]["type"],
	question?: Question
) => {
	const linePayload: PushLineMessagePayload = {
		userIds: [],
		broadcast: undefined,
		event: {
			type: eventType,
			message: {
				text: "",
			},
			question: {
				questionIndex: question?.index || 0,
				questionText: question?.questionText,
			},
		},
	};
	useEffect(() => {
		linePayload.event.question = {
			questionIndex: question?.index || 0,
			questionText: question?.questionText,
		};
	}, [question]);

	/** ボットからメッセージを送信するAPIを叩く */
	const pushLineMessage = async (payload: PushLineMessagePayload) => {
		try {
			const { status, data } = await axios.post("/api/v1/line/push", payload);
			if (status !== 200) {
				throw new Error("pushLineMessage failed");
			}
		} catch (error: any) {
			console.error(error);
			if (error instanceof AxiosError) {
				throw new AxiosError(`push line message: ${error.message}`);
			} else {
				throw new Error(
					`push line message: ${error.message || "unknown error"}`
				);
			}
		}
	};

	return { linePayload, pushLineMessage };
};
export default useLineMessages;
