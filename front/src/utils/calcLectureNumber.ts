/**
 * 送信日時から講義回数を計算する
 * @returns type 講義の前・後半(A or B)
 * @returns number 講義回数
 */
const calcLectureNumber = (date: Date) => {
	/**
	 * 第3Qか第4Qかどうか
	 */
	const isAorB = date <= new Date(2023, 6 - 1, 12) ? "A" : "B";

	// TODO: より効率的な書き方を考える

	if (isAorB === "A") {
		// 第1Qの場合
		if (date <= new Date(2023, 4 - 1, 19)) {
			return { type: isAorB, number: 1 };
		} else if (date <= new Date(2023, 4 - 1, 26)) {
			return { type: isAorB, number: 2 };
		} else if (date <= new Date(2023, 5 - 1, 10)) {
			return { type: isAorB, number: 3 };
		} else if (date <= new Date(2023, 5 - 1, 17)) {
			return { type: isAorB, number: 4 };
		} else if (date <= new Date(2023, 5 - 1, 24)) {
			return { type: isAorB, number: 5 };
		} else if (date <= new Date(2023, 5 - 1, 31)) {
			return { type: isAorB, number: 6 };
		} else if (date <= new Date(2023, 6 - 1, 7)) {
			return { type: isAorB, number: 7 };
		} else if (date <= new Date(2023, 6 - 1, 14)) {
			return { type: isAorB, number: 8 };
		} else {
			return { type: undefined, number: undefined };
		}
	} else {
		// 第2Qの場合
		if (date <= new Date(2023, 6 - 1, 21)) {
			return { type: isAorB, number: 1 };
		} else if (date <= new Date(2023, 6 - 1, 28)) {
			return { type: isAorB, number: 2 };
		} else if (date <= new Date(2023, 7 - 1, 5)) {
			return { type: isAorB, number: 3 };
		} else if (date <= new Date(2023, 7 - 1, 12)) {
			return { type: isAorB, number: 4 };
		} else if (date <= new Date(2023, 7 - 1, 19)) {
			return { type: isAorB, number: 5 };
		} else if (date <= new Date(2023, 7 - 1, 26)) {
			return { type: isAorB, number: 6 };
		} else if (date <= new Date(2023, 8 - 1, 2)) {
			return { type: isAorB, number: 7 };
		} else if (date <= new Date(2023, 8 - 1, 9)) {
			return { type: isAorB, number: 8 };
		} else {
			return { type: undefined, number: undefined };
		}
	}
};

export default calcLectureNumber;