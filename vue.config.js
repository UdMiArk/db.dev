const configureWebpack = require("./webpack.config.js");

module.exports = {
	//publicPath: '/',
	outputDir: "./frontend/web",
	productionSourceMap: false,
	pages: {
		index: {
			entry: "./frontend/main.js",
			template: "./frontend/public/index.html",
			filename: "index.html",
			title: "Хранилище Ресурсов",
			messageJsDisabled: "Увы, без JavaScript вы нынче далеко не уедете",
			minScreenWidth: 320
		}
	},
	configureWebpack,
	devServer: {
		clientLogLevel: "none",
		host: "hot.db.local",
		port: 80,
		proxy: {
			["/api"]: {
				//target: "http://192.168.83.137/",
				target: "http://db.local/",
				changeOrigin: true
			}
		}
	}
};