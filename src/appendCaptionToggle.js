import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

// Translated in PHP and passed in via wp_localize_script - see Assets::enqueueGutenberg().
// No @wordpress/i18n here: it would need wp_set_script_translations() and a JED JSON
// file per bundle per locale.
const { i18n, blocks: SUPPORTED_BLOCKS } = window.MediaLicenseEditor || {};

const ATTRIBUTE = 'mediaLicenseAppendCaption';

const isSupported = (name) => (SUPPORTED_BLOCKS || []).indexOf(name) !== -1;

/**
 * The attribute is deliberately left without a default. An unset attribute is
 * not serialized into the block comment, so existing content stays byte for byte
 * the same and PHP reads it as "append", matching how the plugin behaved before
 * the toggle existed. A default of true would mean the opposite: every block
 * would have to be re-saved to carry it.
 */
addFilter(
	'blocks.registerBlockType',
	'media-license/append-caption-attribute',
	(settings, name) => {
		if (!isSupported(name)) {
			return settings;
		}
		return {
			...settings,
			attributes: {
				...settings.attributes,
				[ATTRIBUTE]: { type: 'boolean' },
			},
		};
	}
);

addFilter(
	'editor.BlockEdit',
	'media-license/append-caption-control',
	createHigherOrderComponent((BlockEdit) => (props) => {
		if (!isSupported(props.name)) {
			return <BlockEdit {...props} />;
		}

		const { attributes, setAttributes } = props;
		const checked = attributes[ATTRIBUTE] !== false;

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody title={i18n.panel_title} initialOpen={false}>
						<ToggleControl
							__nextHasNoMarginBottom
							label={i18n.append_caption}
							help={i18n.append_caption_help}
							checked={checked}
							// Store the opt-out only. Switching back to on clears the
							// attribute again instead of writing true, so a block that
							// was never touched and one that was toggled twice serialize
							// identically.
							onChange={(value) =>
								setAttributes({ [ATTRIBUTE]: value ? undefined : false })
							}
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	}, 'withMediaLicenseAppendCaption')
);
