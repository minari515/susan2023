import { v2 } from "@google-cloud/dialogflow";
import { NextApiRequest, NextApiResponse } from "next";
import clientConfig from "../../libs/clientConfig";

const intentsClient = new v2.IntentsClient(clientConfig);
const agentPath = intentsClient.projectAgentPath(
	process.env.DIALOGFLOW_PROJECT_ID!
);

const DialogflowIntentsList = async (
	req: NextApiRequest,
	res: NextApiResponse
) => {
	const { method } = req;
	const { projectId } = req.query;

	if (projectId !== process.env.DIALOGFLOW_PROJECT_ID) {
		res.status(400).json({ error: "projectId is invalid" });
		return;
	}

	switch (method) {
		case "GET":
			try {
				const response = await getIntentsList();
				res.status(200).json(response);
			} catch (error) {
				res.status(500).json({ error: error });
			}
			break;

		default:
			res.setHeader("Allow", ["GET"]);
			res.status(405).end(`Method ${method} Not Allowed`);
	}
};

export default DialogflowIntentsList;

const getIntentsList = async () => {
	const request = {
		parent: agentPath,
		intentView: "INTENT_VIEW_FULL" as "INTENT_VIEW_FULL",
	};
	try {
		const [response] = await intentsClient.listIntents(request);
		return response;
	} catch (error: any) {
		throw new Error(JSON.stringify(error));
	}
};
