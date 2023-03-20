/**
 * @param strong ユニーク度合い(長さ) default: 5
 * @returns ユニークな文字列
 */
const createUniqueString = (strong: number = 5) => {
	return (
		new Date().getTime().toString(16) +
		Math.floor(strong * Math.random()).toString(16)
	);
};

export default createUniqueString;
