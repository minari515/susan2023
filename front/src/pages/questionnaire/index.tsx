import DefaultLayout from "@/layouts/Default";
import formData from "./formData.json";
import { useContext, useEffect } from "react";
import { useForm } from "react-hook-form";
import useQuestionnaire from "@/hooks/useQuestionnaire";
import { AuthContext } from "@/contexts/AuthContext";
import Loader from "@/components/Loader";

const licart5descriptions = [
	"強く同意しない",
	"同意しない",
	"どちらともいえない",
	"同意する",
	"強く同意する",
];

/**
 * 実験評価アンケートページ
 */
const QuestionnairePage = () => {
	const { user } = useContext(AuthContext);
	/**
	 * フォームの状態を管理する
	 */
	const { register, handleSubmit } = useForm();
	/**
	 * アンケートへの回答・ユーザの回答状況を管理する
	 */
	const {
		isQuestionnaireCompleted,
		checkIsQuestionnaireCompleted,
		postQuestionnaire,
	} = useQuestionnaire();

	// ページ読み込み時にアンケートの回答状況を確認する
	useEffect(() => {
		checkIsQuestionnaireCompleted(user?.token!);
	}, [user?.token]);

	const onSubmit = (data: {}) => {
		postQuestionnaire(user?.token!, data);
	};

	return (
		<DefaultLayout>
			<h1 className='text-xl font-bold p-2'>第3Q実験評価アンケート</h1>

			{isQuestionnaireCompleted ? (
				<div className='text-center'>
					<p>アンケートへのご回答ありがとうございました🙇‍♂️</p>
					<p>引き続きSUSANをよろしくおねがいいたします．</p>
				</div>
			) : (
				<div className='w-full'>
					<section className='w-full max-w-4xl flex flex-col gap-4 items-start px-4 py-2'>
						<div>
							<p>実験に協力していただき，誠にありがとうございます．</p>
							<p>
								以下の注意点をご確認の上，アンケートの回答にご協力お願いします．
							</p>
							<p>
								<span className='font-bold underline underline-offset-[-3px] decoration-4 decoration-sky-500/50'>
									全項目必須
								</span>
								となっておりますが，5分程度でお答えいただけると思いますので，どうぞよろしくお願いいたします。
							</p>
						</div>
						<div className='border-l-4 border-gray-300 pl-2'>
							<h2 className='text-lg font-bold'>被験者の権利について</h2>
							<p>
								アンケートの内容により，
								<span className='font-bold underline underline-offset-[-3px] decoration-4 decoration-sky-500/50'>
									授業の成績に反映されることは一切ありません．
								</span>
							</p>
							<p>率直な意見をお答えください．</p>
						</div>
						<div className='border-l-4 border-gray-300 pl-2'>
							<h2 className='text-lg font-bold'>回答データの利用について</h2>
							<p>
								アンケートを含む実験中に取得したデータは，学会や学術雑誌などに公開される場合があります．
							</p>
							<p>
								この際，いかなる場合においても
								<span className='font-bold underline underline-offset-[-3px] decoration-4 decoration-sky-500/50'>
									被験者のプライバシーに関わる情報は外部に出しません．
								</span>
							</p>
							<p>
								データを公開する場合，被験者個人が特定できない状態にデータを加工し，統計データとして使用いたしますのでご承諾ください．
							</p>
						</div>
					</section>

					<form
						className='w-full max-w-4xl flex flex-col gap-4 items-start p-4'
						onSubmit={handleSubmit(onSubmit)}
					>
						{/* json形式の質問項目データからフォームを作成していく */}
						{formData.map((questionnaire, questionnaireKey) => (
							<div
								key={questionnaireKey}
								className='w-full flex flex-col gap-1 items-start'
							>
								{/* アンケート質問内容 */}
								<h3 className='font-bold'>
									Q{questionnaireKey + 1}.{questionnaire.question}
								</h3>

								{/* リッカート尺度 */}
								{"licart5" in questionnaire.answer && (
									<div className='w-full flex flex-col gap-1 px-6 py-2'>
										{licart5descriptions.map((description, licart) => (
											<label
												key={licart}
												htmlFor={`Q${questionnaireKey + 1}-licart${licart + 1}`}
											>
												<input
													{...register(`Q${questionnaireKey + 1}.licart`, {
														required: true,
													})}
													type='radio'
													value={licart + 1}
													id={`Q${questionnaireKey + 1}-licart${licart + 1}`}
												/>
												{description}
											</label>
										))}
									</div>
								)}

								{/* 回答理由 */}
								{"text" in questionnaire.answer && (
									<div className='w-full px-6 py-2'>
										{"licart5" in questionnaire.answer && (
											<p>✍️回答の理由を自由にお書きください</p>
										)}
										<textarea
											className='w-full h-24 px-1 border-2 rounded-md border-gray-500 dark:text-black'
											{...register(`Q${questionnaireKey + 1}.text`, {
												required: true,
												maxLength: 1000,
											})}
										></textarea>
									</div>
								)}
							</div>
						))}

						{/* 送信ボタン */}
						<div className='w-full flex flex-col items-center'>
							<button className='bg-susanBlue-100 text-white disabled:text-slate-500 disabled:bg-slate-700 active:bg-susanBlue-50 font-bold px-10 py-3 rounded-2xl shadow-inner shadow-susanBlue-1000 outline-none focus:outline-none ease-linear transition-all duration-150'>
								質問内容を送信
							</button>
						</div>
					</form>
				</div>
			)}
		</DefaultLayout>
	);
};

export default QuestionnairePage;
