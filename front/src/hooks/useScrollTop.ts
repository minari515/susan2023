import { useRouter } from "next/router";
import { useEffect } from "react";

/**
 * ページ遷移時にページトップにスクロールする
 */
const useScrollTop = () => {
	const location = useRouter();

	useEffect(() => {
		window.scrollTo(0, 0);
	}, [location.pathname]);
};

export default useScrollTop;