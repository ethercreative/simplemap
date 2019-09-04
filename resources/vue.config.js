module.exports = {
	filenameHashing: false,
	outputDir: '../src/web/assets/map',
	devServer: {
		https: true,
		headers: { "Access-Control-Allow-Origin": "*" },
		disableHostCheck: true,
	},
	configureWebpack: config => {
		config.output.library = 'EtherMaps';
		// config.plugins.push(
		// 	new require('webpack-bundle-analyzer').BundleAnalyzerPlugin()
		// );
	},
};
