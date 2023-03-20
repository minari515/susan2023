import type { ReactNode } from "react";
import { createContext } from "react";
import type { DiscussionMessage, User } from "@/types/models";
import useDiscussionData from "@/hooks/useDiscussion";
import { Dispatch, SetStateAction } from "react";
import type { postDiscussionResponse } from "@/types/response";

class DiscussionContextProps {
	discussionMessages: DiscussionMessage[] = [];
	getDiscussionMessages!: (questionId: number) => void;
	inputtedText: DiscussionMessage["message"] = "";
	setInputtedText!: Dispatch<SetStateAction<DiscussionMessage["message"]>>;
	postImage: File | undefined = undefined;
	setPostImage!: Dispatch<SetStateAction<File | undefined>>;
	messageType: DiscussionMessage["messageType"] = "chat";
	setMessageType!: (messageType: DiscussionMessage["messageType"]) => void;
	postDiscussionMessage!: (
		isUsersQuestion: boolean
	) => Promise<postDiscussionResponse>;
	postDiscussionImage!: (
		isUsersQuestion: boolean
	) => Promise<postDiscussionResponse>;
}

export const DiscussionContext = createContext<DiscussionContextProps>(
	new DiscussionContextProps()
);

type Props = {
	userIdToken: User["token"];
	questionIndex: number;
	children: ReactNode;
};

/**
 * タグ内でディスカッション情報を利用するためのプロバイダ
 * @param userIdToken LIFFのユーザIDトークン
 * @param questionIndex 質問のインデックス
 */
const DiscussionProvider = ({
	userIdToken,
	questionIndex,
	children,
}: Props) => {
	const {
		discussionMessages,
		getDiscussionMessages,
		text,
		setText,
		messageType,
		setMessageType,
		postDiscussionMessage,
		image,
		setImage,
		postDiscussionImage,
	} = useDiscussionData(userIdToken, questionIndex);

	return (
		<DiscussionContext.Provider
			value={{
				discussionMessages,
				getDiscussionMessages,
				inputtedText: text,
				setInputtedText: setText,
				postImage: image,
				setPostImage: setImage,
				messageType,
				setMessageType,
				postDiscussionMessage,
				postDiscussionImage,
			}}
		>
			{children}
		</DiscussionContext.Provider>
	);
};

export default DiscussionProvider;
