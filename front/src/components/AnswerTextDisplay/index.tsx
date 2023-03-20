import InnerUrlText from "@/components/InnerUrlText";
import { QuestionContext } from "@/contexts/QuestionContext";
import { useContext } from "react";

/**
 * @param answerText: 回答テキスト
 * @returns 回答テキストを表示するコンポーネント
 */
const AnswerTextDisplay = () => {
	const { question } = useContext(QuestionContext);
	return !!question?.answerText ? (
		<div className='w-screen flex flex-col items-center p-4 gap-2 text-susanBlue-100 bg-susanBlue-50 dark:text-slate-50 dark:bg-slate-600'>
			<div className='relative w-full max-w-5xl flex flex-col items-center'>
				<h2 className='text-2xl font-bold'>💡 回答</h2>
				<p className='text-xs'>Answer</p>
			</div>
			<InnerUrlText
				settingClass='w-full text-lg whitespace-pre-line break-words leading-relaxed'
				innerText={question.answerText}
			/>
		</div>
	) : (
		<></>
	);
};

export default AnswerTextDisplay;
