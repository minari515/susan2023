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
};

module.exports = nextConfig;
