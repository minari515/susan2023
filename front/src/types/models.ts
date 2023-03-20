export interface User {
	userUid: string;
	token: string;
	type: "student" | "instructor" | "unregistered";
	canAnswer: boolean;
	//displayName: string;
	//pictureUrl: string;
}

export interface IErrorResponse {
	type: string;
	message: string;
}

export interface Question {
	index: number;
	timestamp?: string;
	lectureNumber?: number;
	questionText: string;
	answerText?: string;
	intentName?: string;
	broadcast: boolean;
}

export interface Questions {
	[key: number]: Question;
}

export interface DiscussionMessage {
	index: number;
	timestamp: string;
	userType: User["type"];
	isQuestionersMessage: boolean;
	messageType: "chat" | "image" | "answer";
	message: string;
}

export interface DialogflowIntent {
	intentName: string;
	trainingPhrases: string[];
	responseText: string;
	displayName: string;
	priority: number;
}

export interface DialogflowContext {
	name: string | null;
	lifespanCount: number | null;
}
