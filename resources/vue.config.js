module.exports = {
	filenameHashing: false,
	outputDir: '../src/web/assets/map',
	devServer: {
		https: true,
		headers: { "Access-Control-Allow-Origin": "*" },
		disableHostCheck: true,
	},
};
