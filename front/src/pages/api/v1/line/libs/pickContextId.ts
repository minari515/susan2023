import { DialogflowContext } from "@/types/models";

/**
 * contextIdを抽出する
 * @param context
 * @returns context
 * @description Dialogflowのコンテキスト名はprojects/{projectId}/agent/sessions/{sessionId}/contexts/{contextId}の形式
 */
export const pickContextId = (context: DialogflowContext) => {
	if (!context.name) return context;
	const lastParam = context.name?.split("/").slice(-1)[0];
	context.name = lastParam;
	return context;
};
