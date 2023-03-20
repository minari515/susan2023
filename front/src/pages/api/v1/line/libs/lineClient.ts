import { Client } from "@line/bot-sdk";

// create LINE SDK config from env variables
export const linebotConfig = {
	channelAccessToken: process.env.CHANNEL_ACCESS_TOKEN!,
	channelSecret: process.env.CHANNEL_SECRET!,
};

// create LINE SDK client
export const lineClient = new Client(linebotConfig);

// simple reply function
export const replyText = (token: string, texts: string | string[]) => {
	texts = Array.isArray(texts) ? texts : [texts];
	return lineClient.replyMessage(
		token,
		texts.map((text) => ({ type: "text", text }))
	);
};
