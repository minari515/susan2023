import DefaultLayout from "@/layouts/Default";
import Link from "next/link";
import imagePostNewQuestion from "@/assets/post_new_question.jpg";
import imageLiffQuestion from "@/assets/liff_question.jpeg";
import Image from "next/image";

const howToUse = () => {
	return (
		<DefaultLayout>
			<div className='relative w-full flex flex-col items-center p-4'>
				<h1 className='text-xl font-bold'>📱 SUSANの使い方 💻</h1>
				<span className='font-thin'>How to use</span>
			</div>
			<div className='relative w-full flex flex-col gap-4 px-4'>
				<section className='w-full flex flex-col items-start bg-blue-100 dark:bg-blue-900 p-2 rounded-lg'>
					<span className='text-lg font-bold'>💻 PCからでも利用できます！</span>
					<div className='leading-relaxed'>
						<p>PC版LINEをインストールしたPCからでも利用することができます．</p>
						<p>
							スマートフォン版のようにリッチメニューは表示できませんので，「質問があります」などテキストを入力してご利用ください．
						</p>
						<Link href='https://guide.line.me/ja/services/pc-line.html'>
							<a className='underline font-bold text-blue-600 dark:text-blue-300'>
								🔗 パソコンでLINEを利用する
							</a>
						</Link>
					</div>
				</section>

				<section className='w-full flex flex-col items-center p-2 rounded-lg'>
					<h2 className='text-lg font-bold p-2'>🤔 質問を投稿する</h2>
					<div className='grid grid-cols-1 md:grid-cols-2 gap-4'>
						<div className='leading-relaxed flex flex-col items-center'>
							<p className='w-full'>
								SUSANボットに「質問があります」と入力すると，質問受付状態になります．
								リッチメニューの「質問をする」をタップすることでも可能です．
							</p>
							<p className='w-full'>
								解析システムの都合上，質問文は256字以内でお願いします．
							</p>
							<div className='relative w-full max-w-xs'>
								<Image
									src={imagePostNewQuestion}
									alt='質問投稿の画面例'
									layout='responsive'
								/>
							</div>
						</div>
						<div className='leading-relaxed flex flex-col items-center'>
							<p className='w-full'>
								「質問の詳細へ」から，補足として追加のテキストや画像を投稿することができます．
							</p>
							<p className='w-full'>
								解析システムの都合上，質問文は256字以内でお願いします．
							</p>
							<div className='relative w-full max-w-xs'>
								<Image
									src={imageLiffQuestion}
									alt='投稿質問の詳細画面例'
									layout='responsive'
								/>
							</div>
						</div>
					</div>
				</section>

				<section className='w-full flex flex-col items-center p-2 rounded-lg'>
					<h2 className='text-lg font-bold p-2'>👀 他の学生の質問を見る</h2>
					<div className='leading-relaxed flex flex-col items-center'>
						<p className='w-full'>
							SUSANボットに「みんなの質問を見せて」と入力すると，最新の質問投稿を数件返します．
							リッチメニューの「質問を見る」をタップすることで一覧ページを開くことも出来ます．
						</p>
					</div>
				</section>
			</div>
		</DefaultLayout>
	);
};

export default howToUse;
