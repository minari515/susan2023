import { AuthContext } from "@/contexts/AuthContext";
import { DiscussionContext } from "@/contexts/DiscussionContext";
import { QuestionContext } from "@/contexts/QuestionContext";
import { useContext } from "react";
import InputAnswerField from "./InputAnswerField";
import InputImageField from "./InputImageField";
import InputMessageField from "./InputMessageField";
import MessageTypeSelector from "./MessageTypeSelector";

/**
 * @returns 質問に対するメッセージを入力するフォームおよび送信ボタンを管理するコンポーネント
 * @description 質問に対するメッセージの種類に応じたフォームを表示する
 */
const CreateMessageArea = () => {
	const { user } = useContext(AuthContext);
	const { messageType } = useContext(DiscussionContext);
	const { relevance } = useContext(QuestionContext);
	return user?.type === "instructor" || relevance !== null ? (
		<div className='w-full'>
			<MessageTypeSelector />
			{messageType === "chat" && <InputMessageField />}
			{messageType === "image" && <InputImageField />}
			{messageType === "answer" && <InputAnswerField />}
		</div>
	) : (
		<></>
	);
};
export default CreateMessageArea;
