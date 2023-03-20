/**
 * タイムスタンプの表示形式を整形する
 * @param timestamp mysqlのdate型文字列
 * @returns
 */
const stringToFormatDateTime = (timestamp: string) => {
	const date = new Date(timestamp);
	const year = date.getFullYear();
	const month = (date.getMonth() + 1).toString().padStart(2, "0");
	const day = date.getDate().toString().padStart(2, "0");
	const hour = date.getHours().toString().padStart(2, "0");
	const minute = date.getMinutes().toString().padStart(2, "0");
	const second = date.getSeconds().toString().padStart(2, "0");

	return `${month}/${day} ${hour}:${minute}`;
	//return `${year}/${month}/${day} ${hour}:${minute}:${second}`;
};

export default stringToFormatDateTime;
