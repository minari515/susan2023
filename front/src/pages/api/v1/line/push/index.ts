import { PushLineMessagePayload } from "@/types/payloads";
import type { NextApiRequest, NextApiResponse } from "next";
import { pushAnnounceMessage, pushResponseMessage } from "./handlers";

/** LINEボット側からメッセージを送信する際に利用するAPI */
const LinePushMessageHandler = async (
	req: NextApiRequest,
	res: NextApiResponse
) => {
	if (req.method === "GET") {
		res.status(200).json({ message: "active!" });
	} else if (req.method === "POST"){
		const body = req.body as PushLineMessagePayload;
		console.log(body);
		if (!body.userIds.length && !body.broadcast) {
			res.status(400).json({ error: "userId is required" });
			return;
		}
		if (!body.event) {
			res.status(400).json({ error: "message event is required" });
			return;
		}
		// event handling
		if (body.event.type == "response" || body.event.type == "answer") {
			const pushResponse = await pushResponseMessage(body);
			res.status(200).json({ pushResponse });
		} else if (body.event.type == "announce") {
			await pushAnnounceMessage(body);
			res.status(200).json({ message: "success" });
		} else {
			res.status(400).json({ error: "event type is invalid" });
		}
	} else {
		res.setHeader("Allow", ["GET", "POST"]);
		res.status(405).end(`Method ${req.method} Not Allowed`);
	}
};
export default LinePushMessageHandler;
