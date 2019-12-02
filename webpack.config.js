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
			"@": path.resolve(__dirname, "frontend")
		}
	},
	performance: {
		maxAssetSize: 500000
	},
	plugins: [
		new CopyWebpackPlugin([{from: "frontend/public/", to: ""}])
	]
};