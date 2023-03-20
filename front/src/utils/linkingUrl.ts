/**
 * テキスト中のurlをハイパーリンクに変換する
 * @param text urlが含まれるテキスト
 * @returns urlを含むテキストをリンクに変換したテキスト
 */
const linkingUrl = (text: string) => {
	try {
		return text.replace(
			/(https?:\/\/[^\s]*)/g,
			"<a style='text-decoration-line: underline;' href='$1'>$1</a>"
		);
	} catch (error) {
		if (error instanceof TypeError) {
			console.error;
			console.info(text);
		} else {
			throw error;
		}
		return text;
	}
};

export default linkingUrl;
