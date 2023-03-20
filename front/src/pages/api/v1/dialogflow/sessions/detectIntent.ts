import { DialogflowContext } from "@/types/models";
import { v2 } from "@google-cloud/dialogflow";
import clientConfig from "../libs/clientConfig";

const sessionsClient = new v2.SessionsClient(clientConfig);
const languageCode = "ja";

/**
 * @param uniqueId sessionIDとして使用する一意のID(expected: LINE Message ID)
 * @param inputText ユーザーの発話
 * @param contexts ユーザーの最新のコンテキスト
 * @returns Dialogflowからのレスポンス(NLP解析結果)
 */
export const detectIntent = async (
	uniqueId: string,
	inputText: string,
	contexts: DialogflowContext[]
) => {
	const sessionId = uniqueId;
	const sessionPath = sessionsClient.projectAgentSessionPath(
		process.env.DIALOGFLOW_PROJECT_ID!,
		sessionId
	);

	const inputContexts = contexts.reduce((acc, cur) => {
		if (!cur.name || cur.name == "__system_counters__") return acc;
		cur.name = sessionPath + "/contexts/" + cur.name;
		acc.push(cur);
		return acc;
	}, [] as DialogflowContext[]);

	const request = {
		session: sessionPath,
		queryInput: {
			text: {
				text: inputText,
				languageCode: languageCode,
			},
		},
		queryParams: {
			contexts: inputContexts.length > 0 ? inputContexts : null,
		},
	};
	const [response] = await sessionsClient.detectIntent(request);
	return response;
};
