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
		console.log(typeof req.body);
		let {userIds, broadcast, event}: PushLineMessagePayload = req.body;
		if (typeof req.body === "object"){
			[userIds, broadcast, event] = req.body;
		} else if (typeof req.body === "string") {
			[userIds, broadcast, event] = JSON.parse(req.body);
		}
		// const {userIds, broadcast, event} = req.body as PushLineMessagePayload;

		console.log(userIds);
		console.log(broadcast);
		console.log(event);
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
