/**
 * Created by edward on 31.05.17.
 */
'use strict'

// IIFE Immediately Invoked Function Expression is an anonymus function that is executed immediately
;(function ($, api) {
    /**
     * load license information to core image elements
     */
    api.load_licenses = function () {
        var map = {}
        var ids = []
        $('img').each(function (i, img) {
            // The block this image sits in switched the license info off - see
            // Gutenberg::mark_append_caption_optout(). Skipping here also keeps
            // the id out of the REST request.
            if (img.getAttribute('data-media-license-skip')) {
                return
            }
			// note: 1. scans all img elements and looks for a wp-image-id css class
            var id = api.get_image_id(img)
            if (id) {
                // always check caption
                ids.push(id)
                if (typeof map[id] === typeof undefined) {
                    map[id] = []
                }
                map[id].push(img)
            }
        })

        // get_licenses runs immediately since it is an IIFE
        // _got_licenses is the registered callback that runs for every result of the get_licenses calls
        // since get_licenses batches ids of 10

        // fix duplication problem by removing duplicats
        const sanitizedIds = Array.from(new Set(ids))
        api.get_licenses(sanitizedIds).then(_got_licenses)

        /**
         * build dom from results
         * @param result
         * @private
         */
        function _got_licenses(result) {
            if (result.error) {
                console.error(result)
                return
            }

            if (typeof result.captions === typeof []) {
                for (var id in result.captions) {
                    if (!result.captions.hasOwnProperty(id)) continue
                    // this loop processes all img that share the same id
                    for (var i in map[id]) {
                        if (!map[id].hasOwnProperty(i)) continue

                        var caption = result.captions[id]
                        if (caption.length > 0) {
                            var element = map[id][i]
                            process_image(element, caption)
                        }
                    }
                }
            } else {
                console.error('captions was no array', result)
            }
        }

        /**
         * process image element with caption
         * @param element
         * @param caption
         */
        function process_image(element, caption) {
            var $img = $(element)
            // jquery converts the data-attribute 'data-media-license-block-flag' to 'mediaLicenseBlockFlag'
            if ($img.data('mediaLicenseBlockUseDataAttribute')) {
                add_media_license_as_data_attribute(element, caption)
                collect_block_data_attributes()
                return
            }
            var $figure = $('<figure></figure>')

            // check parent -
            if ($img.parent('figure').length === 1) {
                $figure = $img.parent()
            } else if (
                $img.parent('a').length === 1 &&
                $img.parent().parent('figure').length === 1
            ) {
                $figure = $img.parent().parent()
            } else {
                // jQuery clones $figure into the DOM here, so the original
                // reference is left detached - re-point it at the live node.
                $img.wrap($figure)
                $figure = $img.parent()
            }

            $figure.addClass('media-license__figure')
            // ✅ $figure now exists

            // take over alignment
            if ($img.hasClass('alignright')) {
                $figure.addClass('alignright')
                $img.removeClass('alignright')
            }
            if ($img.hasClass('alignleft')) {
                $figure.addClass('alignleft')
                $img.removeClass('alignleft')
            }
            if ($img.hasClass('aligncenter')) {
                $figure.addClass('aligncenter')
                $img.removeClass('aligncenter')
            }

            const $originalCaption = $figure.find('figcaption')

            console.debug('ML', $originalCaption)

            if ($figure.find('figcaption').length === 0) {
                console.debug('ML', 'figcaption  not found')
                var $caption = $(
                    '<figcaption>' + caption + '</figcaption>'
                ).addClass('wp-caption-text media-license__figcaption')
                // $figure is already the correct ancestor in every case
                // above (bare img, img>figure, img>a>figure, or freshly
                // wrapped) - appending to it directly works regardless of
                // whether the image is wrapped in a link.
                $figure.append($caption)
            } else if (
                $originalCaption.text() !== $('<div>').html(caption).text()
            ) {
                console.debug(
                    'ML',
                    'figcaption found but not equal!',
                    $originalCaption.text(),
                    $('<div>').html(caption).text()
                )

                const originalText = $originalCaption.text().trim()
                const captionFullText = $('<div>')
                    .html(caption)
                    .text()
                    .trimStart()

                if (captionFullText.startsWith(originalText)) {
                    // Case 3A: same caption, license was added — replace without wrapping
                    $originalCaption
                        .addClass('media-license__figcaption')
                        .html(caption)
                } else {
                    // Case 3B: genuinely different captions — keep block caption, append only license info
                    const $captionDiv = $('<div>').html(caption)
                    // Strip the attachment caption: remove the caption span (plugin template)
                    // and any root-level text nodes (theme template)
                    $captionDiv.find('.media-license__caption').remove()
                    $captionDiv
                        .contents()
                        .filter(function () {
                            return this.nodeType === 3
                        })
                        .remove()
                    const licenseHtml = $captionDiv.html()

                    $originalCaption
                        .addClass('media-license__figcaption')
                        .append(licenseHtml)
                }
            }

            if ($figure.find('.media-license__local-figcaption').length > 0) {
                $figure.addClass('has-local-caption')
            }
            if ($figure.find('.media-license__caption').length > 0) {
                $figure.addClass('has-caption')
            }
        }
    }

    function add_media_license_as_data_attribute(element, caption) {
        element.setAttribute('data-media-license-caption', caption)
    }

    function collect_block_data_attributes() {
        const container = document.getElementById(
            'media-license-footer-container'
        )
        if (!container) return

        const imgs = Array.from(
            document.querySelectorAll('img[data-media-license-caption]')
        )

        // Deduplicate by attachment id (wp-image-123) if possible, else by image URL
        const seen = new Set()
        const collectedItems = []

        imgs.forEach((img) => {
            const id = MediaLicense_API.get_image_id(img) // uses your existing helper
            const src = img.currentSrc || img.getAttribute('src') || ''
            const key = id ? `id:${id}` : `src:${src}`
            if (!src) return
            if (seen.has(key)) return
            seen.add(key)

            const licenseHtml = (
                img.getAttribute('data-media-license-caption') || ''
            ).trim()
            if (!licenseHtml) return

            collectedItems.push({
                src,
                alt: img.getAttribute('alt') || '',
                licenseHtml,
            })
        })

        if (collectedItems.length === 0) {
            container.innerHTML = ''
            return
        }

        // Build footer HTML
        const html = collectedItems
            .map((item) => {
                return `
                    <div class="media-license-footer__entry">
                        <img class="media-license-footer__entry__image" src="${escapeAttr(
                                        item.src
                                    )}" alt="${escapeAttr(item.alt)}">
                        <div class="media-license-footer__entry__information">
                            ${item.licenseHtml}
                        </div>
                    </div>
                `
            })
            .join('')

        container.innerHTML = html
    }

    // Small helper to avoid breaking attributes if URLs/alt contain quotes
    function escapeAttr(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
    }

    /**
     * get attachment id from wp-image-{id} class
     * @param img_element
     * @return {*}
     */
    api.get_image_id = function (img_element) {
        var matches = null
        if ((matches = /wp-image-([0-9]+)/g.exec(img_element.className))) {
            return parseInt(matches[1])
        }
        return false
    }

    /**
     * load captions for attachment ids
     * @param attachment_ids
     * @return {{then, trigger}} register a callback with then method. could be called several times.
     */
	// note: calls captions() in classes/REST.php
    api.get_licenses = function (attachment_ids) {
        // this is also a immediately invoked function expression
        var promise = (function () {
            var _cbs = []

            function _then(cb) {
                _cbs.push(cb)
            }

            function _trigger(result) {
                for (var i = 0; i < _cbs.length; i++) {
                    _cbs[i](result)
                }
            }

            return {
                then: _then,
                trigger: _trigger,
            }
        })()

        while (attachment_ids.length) {
            // get 10 attachment captions per call
            var _ids = attachment_ids.splice(0, 10)
            $.ajax({
                method: 'GET',
                url: api.resturl,
                data: {
                    ids: _ids,
                },
            }).done(function (result) {
                promise.trigger(result)
            })
        }

        return promise
    }

    if (api.autoload) {
        // auto load license
        $(function () {
            api.load_licenses()
        })
    }
})(jQuery, MediaLicense_API)
