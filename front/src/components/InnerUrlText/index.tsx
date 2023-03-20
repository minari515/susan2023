import linkingUrl from "@/utils/linkingUrl";
import type { InnerUrlTextProps } from "./types";

/**
 * @param settingClass: 設定クラス名(tailwindcssなどを想定)
 * @param innerText: 表示テキスト
 * @returns テキスト内のURLをリンク<a>に変換したテキスト
 */
const InnerUrlText = ({ settingClass, innerText }: InnerUrlTextProps) => {
	return (
		<p
			className={`${settingClass || ""}`}
			dangerouslySetInnerHTML={{
				__html: linkingUrl(innerText),
			}}
		/>
	);
};

export default InnerUrlText;
