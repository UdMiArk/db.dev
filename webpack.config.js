const path = require("path");
const CopyWebpackPlugin = require("copy-webpack-plugin");

module.exports = {
	externals: {
		//vue: "Vue",
	},
	devServer: {
		clientLogLevel: "none"
	},
	resolve: {
		alias: {
			"@": path.resolve(__dirname, "frontend"),
			"@components": path.resolve(__dirname, "frontend", "components"),
			"@views": path.resolve(__dirname, "frontend", "views"),
			"@assets": path.resolve(__dirname, "frontend", "assets"),
			"@plugins": path.resolve(__dirname, "frontend", "plugins")
		}
	},
	performance: {
		maxAssetSize: 500000
	},
	plugins: [
		new CopyWebpackPlugin([{from: "frontend/public/", to: ""}])
	]
};