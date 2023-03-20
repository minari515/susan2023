import MessageTextArea from "@/components/MessageTextArea";
import { useContext, useEffect, useState } from "react";
import CheckedToggle from "../../CheckedToggle";
import { DiscussionContext } from "@/contexts/DiscussionContext";
import { QuestionContext } from "@/contexts/QuestionContext";
import useDialogflowIntent from "@/hooks/useDialogflowIntent";
import useLineMessages from "@/hooks/useLineMessages";
import { AuthContext } from "@/contexts/AuthContext";
import Loader from "@/components/Loader";

/**
 * @param questionIndex: 質問のインデックス
 * @param question: 質問情報
 * @returns 質問対応の回答メッセージを入力するフォームおよび送信ボタン
 */
const InputAnswerField = () => {
	const [isLoading, setIsLoading] = useState(false);
	const { user } = useContext(AuthContext);
	const {
		question,
		relevance,
		updateAnswerPayload,
		setUpdateAnswerPayload,
		updateQandA,
	} = useContext(QuestionContext);
	const { inputtedText, setInputtedText, postDiscussionMessage } =
		useContext(DiscussionContext);
	const { intent, setIntent, postIntent } = useDialogflowIntent(
		question!.questionText,
		question!.intentName
	);
	const { linePayload, pushLineMessage } = useLineMessages("answer", question);

	useEffect(() => {
		setUpdateAnswerPayload({
			...updateAnswerPayload,
			answerText: inputtedText,
		});
		setIntent({
			...intent,
			responseText: inputtedText,
		});
	}, [inputtedText]);

	const submitHandler = async () => {
		setIsLoading(true);
		try {
			// Dialogflowのインテントを更新，更新後のintentNameを取得
			const updatedIntentName = (await postIntent(question!))!.intentName;
			setUpdateAnswerPayload({
				...updateAnswerPayload,
				intentName: updatedIntentName,
			});

			// DBの質問と回答を更新
			await updateQandA({
				...updateAnswerPayload,
				intentName: updatedIntentName,
			});

			// DBにメッセージを記録
			const res = await postDiscussionMessage(relevance === "questioner");
			// LINEにメッセージを送信
			if (res.pushTo) {
				if (updateAnswerPayload.broadcast) {
					linePayload.userIds = [];
					linePayload.broadcast = true;
				} else {
					linePayload.userIds = res.pushTo;
				}
				linePayload.event.message.text = updateAnswerPayload.answerText!;
				linePayload.event.question!.questionText = question!.questionText;
				await pushLineMessage(linePayload);
				alert("メッセージを送信しました");
			}
			setInputtedText("");
		} catch (error: any) {
			console.error(error);
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
			{user?.type === "instructor" && (
				<CheckedToggle
					checked={updateAnswerPayload.broadcast}
					onChange={() => {
						setUpdateAnswerPayload({
							...updateAnswerPayload,
							broadcast: !updateAnswerPayload.broadcast,
						});
					}}
				>
					<span className='text-sm text-gray-500'>
						質問者以外の学生にも回答を通知する
					</span>
				</CheckedToggle>
			)}
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

export default InputAnswerField;
