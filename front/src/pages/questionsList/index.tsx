import QuestionCard from "@/components/QuestionCard";
import InfiniteScroll from "react-infinite-scroll-component";
import useQuestions from "@/hooks/useQuestions";
import DefaultLayout from "@/layouts/Default";
import noQuestionsImage from "@/assets/waitingQuestion.png";
import Image from "next/image";
import Loader from "@/components/Loader";

/**
 * @returns 質問一覧ページ
 */
const QuestionsListPage = () => {
	const { questions, getQuestionsDataHandler } = useQuestions();

	return (
		<DefaultLayout>
			<div className='relative w-full'>
				{questions.length ? (
					<InfiniteScroll
						dataLength={questions.length} //現在のデータの長さ
						next={getQuestionsDataHandler} // スクロール位置を監視してコールバック（次のデータを読み込ませる）
						hasMore={questions.slice(-1)[0]?.index != 1 || false} // さらにスクロールするかどうか（index:1の質問があればfalseを返すことで無限スクロールを回避）
						loader={<></>} // ローディング中のコンポーネント
						//height={420} // 高さ（なくても良い）
					>
						<ul className='flex flex-col gap-3 p-2'>
							{questions.map((question, key) => (
								<QuestionCard
									key={key}
									id={question.index} // questionId
									timestamp={question.timestamp} // timestamp
									answerStatus={!!question.answerText} // answerStatus
									lectureNumber={question.lectureNumber} // lectureNumber
									questionText={question.questionText} // questionText
								/>
							))}
						</ul>
					</InfiniteScroll>
				) : (
					<div className='relative w-full flex flex-col items-center gap-10 p-10'>
						<h2 className='text-lg font-bold'>まだ質問が投稿されてないよ</h2>
						<div className='relative w-full max-w-sm h-64'>
							<Image src={noQuestionsImage} layout='fill' objectFit='contain' />
						</div>
					</div>
				)}
			</div>
		</DefaultLayout>
	);
};

export default QuestionsListPage;
