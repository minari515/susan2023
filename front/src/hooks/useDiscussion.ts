import axios, { AxiosResponse, AxiosError } from "axios";
import { useEffect, useState } from "react";
import type { DiscussionMessage, User } from "@/types/models";
import { postDiscussionResponse } from "@/types/response";

/**
 * 質問対応のメッセージ群の管理
 */
const useDiscussionData = (
	userIdToken: User["token"],
	questionIndex: number
) => {
	// メッセージ群を取得・保持する
	const [discussionMessages, setDiscussionMessages] = useState<
		DiscussionMessage[]
	>([]);
	useEffect(() => {
		getDiscussionMessages(questionIndex);
	}, []);

	/**
	 * データベースからメッセージ群を取得する
	 */
	const getDiscussionMessages = async (questionIndex: number) => {
		try {
			const { status, data } = await axios.get<DiscussionMessage[]>(
				`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/discussions/${questionIndex}`
			);
			if (status === 200) {
				setDiscussionMessages(data);
			} else {
				throw new Error("データベースからの取得に失敗しました");
			}
		} catch (error: any) {
			if (error instanceof AxiosError) {
				throw new AxiosError(`get discussion: ${error.message}`);
			} else {
				throw new Error(`get discussion: ${error.message || "unknown error"}`);
			}
		}
	};

	// メッセージを送信する
	const [messageType, setMessageType] =
		useState<DiscussionMessage["messageType"]>("chat");

	// テキスト
	const [text, setText] = useState<DiscussionMessage["message"]>("");

	// TODO: postDiscussionImageと統合する
	/** ディスカッションに新規メッセージを追加する */
	const postDiscussionMessage = async (isUsersQuestion: boolean) => {
		try {
			const { status, data } = await axios.post<postDiscussionResponse>(
				`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/discussions/${messageType}`,
				{
					index: questionIndex,
					userIdToken: userIdToken,
					message: text,
					isUsersQuestion: isUsersQuestion,
				}
			);
			if (status == 201) {
				setDiscussionMessages([...discussionMessages, data.insertedMessage]);
				return data;
			} else {
				throw new Error("メッセージの送信に失敗しました");
			}
		} catch (error: any) {
			console.error(error);
			if (error instanceof AxiosError) {
				throw new AxiosError(error.message);
			} else {
				throw new Error(
					error.message || "サーバー通信時にエラーが発生しました．"
				);
			}
		}
	};

	// 画像
	const [image, setImage] = useState<File>();
	/** ディスカッションに新規投稿(画像)を追加する */
	const postDiscussionImage = async (isUsersQuestion: boolean) => {
		const formData = new FormData();
		formData.append("index", questionIndex.toString());
		formData.append("userIdToken", userIdToken);
		formData.append("isUsersQuestion", isUsersQuestion.toString());
		formData.append("file", image!);
		try {
			const { status, data } = await axios.post<postDiscussionResponse>(
				`${process.env.NEXT_PUBLIC_API_BASE_URL}/api/v2/discussions/image`,
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				}
			);
			if (status !== 201) throw new Error("画像の送信に失敗しました");
			setDiscussionMessages([...discussionMessages, data.insertedMessage]);
			return data;
		} catch (error: any) {
			if (error instanceof AxiosError) {
				throw new AxiosError(`upload image: ${error.message}`);
			} else {
				throw new Error(`upload image: ${error.message || "unkown error"}`);
			}
		}
	};

	return {
		discussionMessages,
		getDiscussionMessages,
		text,
		setText,
		messageType,
		setMessageType,
		postDiscussionMessage,
		image,
		setImage,
		postDiscussionImage,
	};
};

export default useDiscussionData;
