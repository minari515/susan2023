import { DialogflowIntent } from "@/types/models";
import type { NextApiRequest, NextApiResponse } from "next";
import { v2 } from "@google-cloud/dialogflow";
import clientConfig from "../libs/clientConfig";

const intentsClient = new v2.IntentsClient(clientConfig);
const agentPath = intentsClient.projectAgentPath(
	process.env.DIALOGFLOW_PROJECT_ID!
);

const DialogflowIntentHandler = async (
	req: NextApiRequest,
	res: NextApiResponse
) => {
	const { method, query, body } = req;
	switch (method) {
		case "GET":
			if (!query.intentName) {
				res.status(400).json({ error: "intentName is required" });
				return;
			}
			try {
				const response = await getIntent(
					query.intentName as DialogflowIntent["intentName"]
				);
				res.status(200).json(response);
			} catch (error) {
				res.status(404).end("Intent not found");
			}
			break;

		case "POST":
			try {
				const response = !body.intentName
					? await createQuestionIntent(
							Number(body.questionIndex),
							body.trainingPhrases as DialogflowIntent["trainingPhrases"],
							body.responseText as DialogflowIntent["responseText"],
							Number(body.lectureNumber)
					  )
					: await updateQuestionIntent(
							body.trainingPhrases as DialogflowIntent["trainingPhrases"],
							body.responseText as DialogflowIntent["responseText"],
							body.intentName as DialogflowIntent["intentName"]
					  );
				res.status(201).json(response);
			} catch (error) {
				res
					.status(500)
					.json({ error: JSON.stringify(error), requestBody: body });
			}
			break;

		/* case "PUT":
			if (!query.intentName) {
				res.status(400).json({ error: "intentName is required" });
				return;
			}
			try {
				const response = await updateQuestionIntent(req.body);
				res.status(200).json(response);
			} catch (error) {
				res.status(500).json({ error: error });
			}
			break;
			 */

		default:
			res.setHeader("Allow", ["GET", "POST"]);
			res.status(405).end(`Method ${method} Not Allowed`);
	}
};

const getIntent = async (intentName: DialogflowIntent["intentName"]) => {
	const request = {
		parent: agentPath,
		intentView: "INTENT_VIEW_FULL" as "INTENT_VIEW_FULL",
		name: intentName,
	};
	try {
		const intent = await intentsClient.getIntent(request);
		const trainingPhrases = intent[0].trainingPhrases!.map((phrase) => {
			return phrase.parts![0].text;
		});
		return {
			intentName: intent[0].name,
			trainingPhrases,
			responseText: intent[0].messages![0].text!.text![0],
			displayName: intent[0].displayName,
			priority: intent[0].priority,
		} as DialogflowIntent;
	} catch (error: any) {
		throw new Error(JSON.stringify(error));
	}
};

const createQuestionIntent = async (
	questionIndex: number,
	trainingPhrases: DialogflowIntent["trainingPhrases"],
	responseText: DialogflowIntent["responseText"],
	lectureNumber?: number
) => {
	const createIntentRequest = {
		parent: agentPath,
		intentView: "INTENT_VIEW_FULL" as "INTENT_VIEW_FULL",
		intent: {
			displayName:
				`0000${questionIndex}`.slice(-4) +
				`_${trainingPhrases[0].slice(0, 10)}`,
			parentFollowupIntentName: `projects/${process.env.DIALOGFLOW_PROJECT_ID}/agent/intents/5ea1a85b-07a2-4e6e-b242-9a9e7738f50e`,
			inputContextNames: [
				`projects/${process.env.DIALOGFLOW_PROJECT_ID}/agent/sessions/-/contexts/QuestionStart-followup`,
			],
			outputContexts: [
				{
					name: `projects/${process.env.DIALOGFLOW_PROJECT_ID}/agent/sessions/-/contexts/SendAutoAnswer`,
					lifespanCount: 1,
					parameters: null,
				},
			],
			trainingPhrases: trainingPhrases.map((phrase: string) => {
				return {
					parts: [
						{
							text: phrase,
						},
					],
				};
			}),
			action: "AnswerToTheQuestion",
			parameters: [
				{
					displayName: "questionIndex",
					value: `${questionIndex}`,
				},
				{
					displayName: "originQuestion",
					value: trainingPhrases[0],
				},
				{
					displayName: "lectureNumber",
					value: `${lectureNumber || ""}`,
				},
			],
			messages: [
				{
					text: {
						text: [responseText],
					},
				},
			],
		},
	};
	try {
		const intent = await intentsClient.createIntent(createIntentRequest);
		return {
			intentName: intent[0].name,
			trainingPhrases,
			responseText: intent[0].messages![0].text!.text![0],
			displayName: intent[0].displayName,
			priority: intent[0].priority,
		} as DialogflowIntent;
	} catch (error: any) {
		throw new Error(JSON.stringify(error));
	}
};

const updateQuestionIntent = async (
	trainingPhrases: DialogflowIntent["trainingPhrases"],
	responseText: DialogflowIntent["responseText"],
	intentName: DialogflowIntent["intentName"]
) => {
	try {
		const existsIntent = await intentsClient.getIntent({ name: intentName });
		existsIntent[0].trainingPhrases = trainingPhrases.map((phrase: string) => {
			return {
				parts: [
					{
						text: phrase,
					},
				],
			};
		});
		existsIntent[0].messages![0].text!.text![0] = responseText;
		const updateIntentRequest = {
			intent: existsIntent[0],
			updateMask: {
				paths: ["training_phrases", "messages"],
			},
			intentView: "INTENT_VIEW_FULL" as "INTENT_VIEW_FULL",
		};
		const updatedIntent = await intentsClient.updateIntent(updateIntentRequest);
		return {
			intentName: updatedIntent[0].name,
			trainingPhrases,
			responseText: updatedIntent[0].messages![0].text!.text![0],
			displayName: updatedIntent[0].displayName,
			priority: updatedIntent[0].priority,
		} as DialogflowIntent;
	} catch (error: any) {
		throw new Error(JSON.stringify(error));
	}
};

export default DialogflowIntentHandler;
