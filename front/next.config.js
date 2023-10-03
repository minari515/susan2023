/** @type {import('next').NextConfig} */
const nextConfig = {
	reactStrictMode: true,
	webpackDevMiddleware: (config) => {
		config.watchOptions = {
			poll: 800,
			aggregateTimeout: 300,
		};
		return config;
	},
	distDir: "build",
	optimizeFonts: true,
	// 全ての API routes にマッチ
	async headers() {
		return [
			{
				source: "/api/:path*",
				headers: [
					{
						// 許可するメソッド
						key: "Access-Control-Allow-Methods",
						value: "GET,OPTIONS,POST",
					},
					{
						// 許可するリクエストヘッダ
						key: "Access-Control-Allow-Headers",
						value: "Content-Type",
					},
				],
			},
		];
	},
};

module.exports = nextConfig;
