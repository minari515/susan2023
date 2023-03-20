/**
 * 送信日時から講義回数を計算する
 * @returns type 講義の前・後半(A or B)
 * @returns number 講義回数
 */
const calcLectureNumber = (date: Date) => {
	/**
	 * 第3Qか第4Qかどうか
	 */
	const isAorB = date <= new Date(2022, 12 - 1, 6) ? "A" : "B";

	// TODO: より効率的な書き方を考える

	if (isAorB === "A") {
		// 第3Qの場合
		if (date <= new Date(2022, 10 - 1, 12)) {
			return { type: isAorB, number: 1 };
		} else if (date <= new Date(2022, 10 - 1, 19)) {
			return { type: isAorB, number: 2 };
		} else if (date <= new Date(2022, 10 - 1, 26)) {
			return { type: isAorB, number: 3 };
		} else if (date <= new Date(2022, 11 - 1, 9)) {
			return { type: isAorB, number: 4 };
		} else if (date <= new Date(2022, 11 - 1, 16)) {
			return { type: isAorB, number: 5 };
		} else if (date <= new Date(2022, 11 - 1, 23)) {
			return { type: isAorB, number: 6 };
		} else if (date <= new Date(2022, 11 - 1, 30)) {
			return { type: isAorB, number: 7 };
		} else if (date <= new Date(2022, 12 - 1, 5)) {
			return { type: isAorB, number: 8 };
		} else {
			return { type: undefined, number: undefined };
		}
	} else {
		// 第4Qの場合
		if (date <= new Date(2022, 12 - 1, 14)) {
			return { type: isAorB, number: 1 };
		} else if (date <= new Date(2022, 12 - 1, 21)) {
			return { type: isAorB, number: 2 };
		} else if (date <= new Date(2022, 12 - 1, 28)) {
			return { type: isAorB, number: 3 };
		} else if (date <= new Date(2023, 1 - 1, 4)) {
			return { type: isAorB, number: 4 };
		} else if (date <= new Date(2023, 1 - 1, 11)) {
			return { type: isAorB, number: 5 };
		} else if (date <= new Date(2023, 1 - 1, 18)) {
			return { type: isAorB, number: 6 };
		} else if (date <= new Date(2023, 1 - 1, 25)) {
			return { type: isAorB, number: 7 };
		} else if (date <= new Date(2023, 2 - 1, 4)) {
			return { type: isAorB, number: 8 };
		} else {
			return { type: undefined, number: undefined };
		}
	}
};

export default calcLectureNumber;
