import type { QuestionCardProps } from "./types";
import { useRouter } from "next/router";
import CheckIcon from "@/assets/task_alt_FILL1_wght400_GRAD0_opsz48.svg";
import stringToFormatDateTime from "@/utils/stringToFormatDateTime";

/**
 * @param id: 質問のID
 * @param timestamp: 質問の投稿日時
 * @param answerStatus: 回答の状態
 * @param questionText: 質問文
 * @returns 質問一覧に表示するカードコンポーネント
 */
const QuestionCard = ({
	id,
	timestamp,
	answerStatus,
	lectureNumber,
	questionText,
}: QuestionCardProps) => {
	const router = useRouter();
	return (
		<li
			onClick={() => router.push(`/question/${id}`)}
			className='relative list-none cursor-pointer flex flex-col gap-y-2 border shadow-sm rounded-xl p-4 md:p-5 dark:border-gray-700 dark:shadow-slate-700/[.7]'
		>
			<div className='flex flex-row gap-x-4 items-center'>
				<span className='text-sm font-semibold inline-block py-1 px-2 rounded-lg text-indigo-600 bg-indigo-200'>
					{lectureNumber ? `第${lectureNumber}回` : "未分類"}
				</span>
				<time>{stringToFormatDateTime(timestamp!)}</time>
				{answerStatus && (
					<span className='absolute top-1 right-1 opacity-40 -z-10'>
						<CheckIcon
							width='70'
							height='70'
							viewBox='0 0 48 48'
							fill='#2ad3a6'
							alt='answer status'
						/>
					</span>
				)}
			</div>
			<div>
				<p>{questionText}</p>
			</div>
		</li>
	);
};

export default QuestionCard;
