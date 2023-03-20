import MessageTextArea from "@/components/MessageTextArea";
import { useContext, useState } from "react";
import { DiscussionContext } from "@/contexts/DiscussionContext";
import useLineMessages from "@/hooks/useLineMessages";
import { QuestionContext } from "@/contexts/QuestionContext";
import Loader from "@/components/Loader";

/**
 * @returns 質問対応のメッセージを入力するフォームおよび送信ボタン
 */
const InputMessageField = () => {
	const [isLoading, setIsLoading] = useState(false);
	const { question, relevance } = useContext(QuestionContext);
	const { inputtedText, setInputtedText, postDiscussionMessage } =
		useContext(DiscussionContext);
	const { linePayload, pushLineMessage } = useLineMessages(
		"response",
		question
	);

	const submitHandler = async () => {
		setIsLoading(true);
		try {
			// DBにメッセージを記録
			const res = await postDiscussionMessage(relevance === "questioner");
			// LINEにメッセージを送信
			if (res.pushTo) {
				linePayload.userIds = res.pushTo;
				linePayload.event.message.text = inputtedText;
				await pushLineMessage(linePayload);
				alert("メッセージを送信しました");
			}
			// メッセージ入力欄を空にする
			setInputtedText("");
		} catch (error: any) {
			console.error(error);
			alert(`エラーが発生しました. 
			Error:${JSON.stringify(error)}`);
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<div className='relative w-full flex flex-col items-center gap-2 p-4 '>
			{isLoading && <Loader />}
			<MessageTextArea text={inputtedText} setText={setInputtedText} />
			<button
				className='bg-susanBlue-100 text-white disabled:text-slate-500 disabled:bg-slate-700 active:bg-susanBlue-50 font-bold px-8 py-2 rounded-2xl shadow-inner shadow-susanBlue-1000 outline-none focus:outline-none ease-linear transition-all duration-150'
				onClick={submitHandler}
				disabled={!inputtedText}
			>
				送信
			</button>
		</div>
	);
};

export default InputMessageField;
