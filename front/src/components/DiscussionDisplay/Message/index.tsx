import InnerUrlText from "@/components/InnerUrlText";
import stringToFormatDateTime from "@/utils/stringToFormatDateTime";
import { MessageProps } from "./types";

/**
 * @returns 質問対応メッセージ1件分を表示するコンポーネント
 */
const Message = ({ discussionMessage }: MessageProps) => {
	const messageClass = () => {
		if (discussionMessage.isQuestionersMessage) {
			return "relative max-w-full p-2 rounded-lg break-words bg-forest-green dark:bg-slate-500 after:content-[''] after:absolute after:bottom-4 after:-right-1.5 after:w-3 after:h-3 after:rotate-45 after:bg-forest-green dark:after:bg-slate-500";
		} else if (discussionMessage.userType === "student") {
			return "relative max-w-full p-2 rounded-lg break-words bg-neutral-200  dark:bg-slate-500  before:content-[''] before:absolute before:bottom-4 before:-left-1.5 before:w-3 before:h-3 before:rotate-45 before:bg-neutral-200 dark:before:bg-slate-500";
		} else if (discussionMessage.userType === "instructor") {
			return "relative max-w-full p-2 rounded-lg break-words text-slate-100 bg-susanBlue-100    before:content-[''] before:absolute before:bottom-4 before:-left-1.5 before:w-3 before:h-3 before:rotate-45 before:bg-susanBlue-100";
		}
	};
	return (
		<div
			className={`w-full flex flex-col ${
				discussionMessage.isQuestionersMessage ? "items-end" : "items-start"
			}`}
		>
			<div className={messageClass()}>
				{discussionMessage.messageType != "image" ? (
					<InnerUrlText
						innerText={discussionMessage.message}
						settingClass='w-full whitespace-pre-line break-words leading-relaxed'
					/>
				) : (
					<img
						src={`https://www2.yoslab.net/${discussionMessage.message}`}
						alt={`${discussionMessage.userType}'s image message`}
					/>
				)}
			</div>
			<p className='text-[0.5rem] text-slate-600 dark:text-slate-400'>
				{stringToFormatDateTime(discussionMessage.timestamp)}
			</p>
		</div>
	);
};

export default Message;
