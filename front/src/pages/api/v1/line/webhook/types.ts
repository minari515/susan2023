import type { postMessageLogParams } from "@/types/payloads";
import type { MessageAPIResponseBase } from "@line/bot-sdk/dist/types";

export interface botResponse {
	messageAPIResponse?: MessageAPIResponseBase;
	messageLog: postMessageLogParams;
}
