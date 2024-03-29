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
		const payload : PushLineMessagePayload = req.body;
		const {userIds, broadcast, event} = payload;

		if (!userIds.length && !broadcast) {
			res.status(400).json({ error: "userId is required" });
			return;
		}
		if (!event) {
			res.status(400).json({ error: "message event is required" });
			return;
		}
		// event handling
		if (event.type == "response" || event.type == "answer") {
			const pushResponse = await pushResponseMessage({userIds, broadcast, event});
			res.status(200).json({ pushResponse });
		} else if (event.type == "announce") {
			await pushAnnounceMessage({userIds, broadcast, event});
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
