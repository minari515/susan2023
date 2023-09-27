import AnswerTextDisplay from "@/components/AnswerTextDisplay";
import DiscussionDisplay from "@/components/DiscussionDisplay";
import CreateMessageArea from "@/components/CreateMessageArea";
import QuestionTextDisplay from "@/components/QuestionTextDisplay";
import { AuthContext } from "@/contexts/AuthContext";
import DiscussionProvider from "@/contexts/DiscussionContext";
import QuestionProvider from "@/contexts/QuestionContext";
import { useRouter } from "next/router";
import { useContext } from "react";
import DefaultLayout from "@/layouts/Default";
import Loader from "@/components/Loader";

/**
 * @returns 質問詳細ページ
 * @description クエリパラメータよりquestionIdを指定する必要がある
 */
const QuestionDetailsPage = () => {
	const router = useRouter();
	const { questionId } = router.query;
	const { user } = useContext(AuthContext);

	return !!user ? (
		<DefaultLayout>
			<QuestionProvider
				userIdToken={user.token}
				questionIndex={Number(questionId)}
			>
				<QuestionTextDisplay />
				<AnswerTextDisplay />
				<DiscussionProvider
					userIdToken={user.token}
					questionIndex={Number(questionId)}
				>
					<DiscussionDisplay />
					<CreateMessageArea />
				</DiscussionProvider>
			</QuestionProvider>
		</DefaultLayout>
	) : (
		<Loader />
	);
};

export default QuestionDetailsPage;
