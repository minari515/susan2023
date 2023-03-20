import Loader from "@/components/Loader";
import { DiscussionContext } from "@/contexts/DiscussionContext";
import { QuestionContext } from "@/contexts/QuestionContext";
import { useContext, useState } from "react";

/**
 * @param questionIndex: 質問のインデックス
 * @returns 質問対応の画像を入力するフォームおよび送信ボタン
 */
const InputImageField = () => {
	const [isLoading, setIsLoading] = useState(false);
	const { relevance } = useContext(QuestionContext);
	const { postImage, setPostImage, postDiscussionImage } =
		useContext(DiscussionContext);

	const validateImageSize = (image: File) => {
		if (image.size > 52428800) {
			alert(`ファイルの上限サイズ50MBを超えています(${image.size}Bytes)`);
			return false;
		} else {
			return true;
		}
	};

	const submitHandler = async () => {
		setIsLoading(true);
		try {
			await postDiscussionImage(relevance === "questioner");
			setPostImage(undefined);
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
			<div className='relative w-full'>
				<input
					id='input-image'
					type='file'
					accept='image/*'
					className='absolute top-0 left-0 w-full h-full opacity-0 cursor-pointer'
					onChange={(e) =>
						validateImageSize(e.target.files![0]) &&
						setPostImage(e.target.files![0])
					}
				/>
				<div className='flex items-center justify-center w-full min-h-[6rem] rounded-lg p-2 border border-gray-300 dark:bg-slate-600'>
					{!!postImage && <img src={URL.createObjectURL(postImage!)} />}
					<label htmlFor='input-image' className='input-image-label'>
						{!postImage ? "📁 画像(.jpeg, .png)を選択" : ""}
					</label>
				</div>
			</div>
			<button
				className='bg-susanBlue-100 text-white disabled:text-slate-500 disabled:bg-slate-700 active:bg-susanBlue-50 font-bold px-8 py-2 rounded-2xl shadow-inner shadow-susanBlue-1000 outline-none focus:outline-none ease-linear transition-all duration-150'
				onClick={submitHandler}
				disabled={!postImage}
			>
				送信
			</button>
		</div>
	);
};

export default InputImageField;
