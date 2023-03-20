import type { Question } from "@/types/models";

export interface QuestionCardProps {
	id: number;
	timestamp: Question["timestamp"];
	answerStatus: boolean;
	lectureNumber?: number;
	questionText: Question["questionText"];
}
