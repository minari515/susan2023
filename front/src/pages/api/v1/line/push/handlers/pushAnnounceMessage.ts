import { PushLineMessagePayload } from "@/types/payloads";
import { lineClient } from "../../libs";
import { announceFlexMessage } from "../../libs/flexMessages";

/**
 * アナウンスをLINEに送信する
 * @note event.type == "announce"
 */
const pushAnnounceMessage = async ({
	userIds,
	broadcast,
	event,
}: PushLineMessagePayload) => {
	const message = announceFlexMessage(event.message.text);
	broadcast
		? await lineClient.broadcast(message)
		: await lineClient.multicast(userIds, message);
};

export default pushAnnounceMessage;
