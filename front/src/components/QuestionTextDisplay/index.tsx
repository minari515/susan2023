import { QuestionContext } from "@/contexts/QuestionContext";
import { useContext } from "react";
import InnerUrlText from "../InnerUrlText";

/**
 * @param questionText: 質問文
 * @param lectureNumber: 講義回
 * @returns 質問文を表示するカードコンポーネント
 */
const QuestionTextDisplay = () => {
	const { question } = useContext(QuestionContext);

	return (
		<div className='w-screen flex flex-col items-center p-4 gap-2 text-slate-50 bg-susanBlue-100'>
			<div className='relative w-full max-w-5xl flex flex-col items-center'>
				<span className='absolute top-0 left-0 text-sm font-semibold inline-block py-1 px-2 rounded-lg text-indigo-600 bg-indigo-200'>
					{question?.lectureNumber
						? `第${question!.lectureNumber}回`
						: "未分類"}
				</span>
				<h2 className='text-2xl font-bold'>🤔 質問</h2>
				<p className='text-xs'>Question</p>
			</div>
			<InnerUrlText
				settingClass='w-full text-lg whitespace-pre-line break-words leading-relaxed'
				innerText={question?.questionText || ""}
			/>
		</div>
	);
};

export default QuestionTextDisplay;
