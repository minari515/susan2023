module.exports = {
	content: [
		// class属性を含む全てのファイルを指定する必要がある(jsx,htmlなど)
		"./src/pages/**/*.{js,ts,jsx,tsx}",
		"./src/components/**/*.{js,ts,jsx,tsx}",
	],
	theme: {
		extend: {
			colors: {
				susanBlue: {
					50: "#e0eafe",
					100: "#284275",
					500: "#182846",
					1000: "#04070c",
				},
				"forest-green": "#06C755", // LINE official color
			},
		},
	},
	plugins: [],
};
