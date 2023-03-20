import type { NextApiRequest, NextApiResponse } from "next";
import type { Middleware } from "@line/bot-sdk/dist/middleware";
import { middleware as _middleware } from "@line/bot-sdk";
import { linebotConfig } from "./lineClient";

/**
 * Helper method to wait for a middleware to execute before continuing
 * And to throw an error when an error happens in a middleware
 * @note The middleware returned by middleware() parses body and checks signature validation, so you do not need to use validateSignature() directly.
 * @reference https://nextjs.org/docs/api-routes/api-middlewares
 */
export const runMiddleware = (
	req: NextApiRequest,
	res: NextApiResponse,
	fn: Middleware
) => {
	return new Promise((resolve, reject) => {
		fn(req, res, (result) => {
			if (result instanceof Error) {
				return reject(result);
			}
			return resolve(result);
		});
	});
};

/**
 * LINEボットのWebhookエンドポイントのミドルウェア
 * 署名検証とbodyのパースを行う
 * @ref https://line.github.io/line-bot-sdk-nodejs/api-reference/middleware.html
 */
export const middleware = _middleware(linebotConfig);
