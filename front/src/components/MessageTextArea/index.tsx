import { MessageTextAreaProps } from "./types";

/**
 * @param setText: テキストエリアの値をセットする関数
 * @returns テキストエリア
 */
const MessageTextArea = ({ text, setText }: MessageTextAreaProps) => {
	return (
		<>
			<textarea
				className='w-full h-24 p-2 border border-gray-400 rounded-lg dark:bg-slate-600 focus:outline-none focus:border-sky-400'
				placeholder='メッセージを入力'
				value={text}
				onChange={(e) => setText(e.target.value)}
			/>
		</>
	);
};

export default MessageTextArea;
