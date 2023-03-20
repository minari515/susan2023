import { User } from "@line/bot-sdk";
import { DiscussionMessage, Question } from "./models";

export interface postDiscussionResponse {
	insertedMessage: DiscussionMessage;
	pushTo?: User["userId"][];
}

export interface postNewQuestionResponse {
	questionIndex: Question["index"];
	discussionIndex?: number;
	assignedStudents: User["userId"][];
}
