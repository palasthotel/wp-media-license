const defaultConfig = require( './node_modules/@wordpress/scripts/config/webpack.config.js' );
const path = require('path')

module.exports = {
	...defaultConfig,
	devtool: 'source-map',
	entry: {
		'media-license': path.resolve(__dirname, './src/gutenberg.js'),
	},
	output: {
		path: path.resolve(__dirname, './public/js/gutenberg/.'),
	},
}