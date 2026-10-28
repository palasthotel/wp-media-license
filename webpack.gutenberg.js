const defaultConfig = require( './node_modules/@wordpress/scripts/config/webpack.config.js' );
const { CycloneDxWebpackPlugin } = require( '@cyclonedx/webpack-plugin' );
const path = require('path')

const config = {
	...defaultConfig,
	devtool: 'source-map',
	entry: {
		'media-license': path.resolve(__dirname, './src/gutenberg.js'),
	},
	output: {
		path: path.resolve(__dirname, './public/dist/.'),
	},
}

/**
 * Production builds also write a CycloneDX SBOM of what webpack bundled
 * (public/dist/cyclonedx/bom.json). The release workflows from
 * palasthotel/github-workflows collect it for Dependency-Track and keep it
 * out of the plugin payload, so it is never deployed to wordpress.org.
 */
function withSbom(config) {
	if ( config.mode !== 'production' ) {
		return config;
	}
	return {
		...config,
		plugins: [
			...config.plugins,
			new CycloneDxWebpackPlugin({
				specVersion: '1.6',
				outputLocation: './cyclonedx',
				includeWellknown: false,
				reproducibleResults: true,
			}),
		],
	};
}

module.exports = withSbom( config );
