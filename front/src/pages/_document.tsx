import { Head, Html, Main, NextScript } from "next/document";

const MyDocument = () => {
	return (
		<Html lang='ja-JP'>
			<Head>
				<meta name='application-name' content='SUSAN ver.Next.js' />
				<link rel='stylesheet' />
			</Head>
			<body className='text-black bg-slate-50 dark:text-slate-100 dark:bg-black'>
				<Main />
				<NextScript />
			</body>
		</Html>
	);
};

export default MyDocument;
