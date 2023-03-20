import { postMessageLogParams } from "@/types/payloads";
import axios from "axios";
import { pickContextId } from "../pickContextId";

/**
 * DBに対話ログを登録するAPIを叩く
 */
const postMessageLog = async ({
	userId,
	messageType,
	message,
	userType,
	context,
}: postMessageLogParams) => {
	const sliceContext = context ? pickContextId(context) : context;
	try {
		const { status, data } = await axios.post(
			`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/line/message`,
			{
				userId,
				messageType,
				message,
				sender: userType,
				contextName: sliceContext?.name,
				lifespanCount: sliceContext?.lifespanCount,
			}
		);
		return status;
	} catch (error: any) {
		throw error;
	}
};
export default postMessageLog;
