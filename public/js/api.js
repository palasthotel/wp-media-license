/**
 * Created by edward on 31.05.17.
 */
'use strict'

// IIFE Immediately Invoked Function Expression is an anonymus function that is executed immediately
;(function (api) {
    /**
     * load license information to core image elements
     */
    api.load_licenses = function () {
        var map = {}
        var ids = []
        document.querySelectorAll('img').forEach(function (img) {
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
            var img = element
            if (is_truthy_data(img.dataset.mediaLicenseBlockUseDataAttribute)) {
                add_media_license_as_data_attribute(element, caption)
                collect_block_data_attributes()
                return
            }

            var figure
            var parent = img.parentElement
            if (parent && parent.matches('figure')) {
                figure = parent
            } else if (
                parent &&
                parent.matches('a') &&
                parent.parentElement &&
                parent.parentElement.matches('figure')
            ) {
                figure = parent.parentElement
            } else {
                figure = document.createElement('figure')
                img.parentNode.insertBefore(figure, img)
                figure.appendChild(img)
            }

            figure.classList.add('media-license__figure')

            // take over alignment
            ;['alignright', 'alignleft', 'aligncenter'].forEach(function (align) {
                if (img.classList.contains(align)) {
                    figure.classList.add(align)
                    img.classList.remove(align)
                }
            })

            const originalCaptions = Array.from(figure.querySelectorAll('figcaption'))

            console.debug('ML', originalCaptions)

            if (originalCaptions.length === 0) {
                console.debug('ML', 'figcaption  not found')
                var figcaption = document.createElement('figcaption')
                figcaption.innerHTML = caption
                figcaption.classList.add('wp-caption-text', 'media-license__figcaption')
                // figure is already the correct ancestor in every case
                // above (bare img, img>figure, img>a>figure, or freshly
                // wrapped) - appending to it directly works regardless of
                // whether the image is wrapped in a link.
                figure.appendChild(figcaption)
            } else {
                const originalFullText = originalCaptions
                    .map(function (el) {
                        return el.textContent
                    })
                    .join('')
                const captionText = html_to_text(caption)

                if (originalFullText !== captionText) {
                    console.debug(
                        'ML',
                        'figcaption found but not equal!',
                        originalFullText,
                        captionText
                    )

                    const originalText = originalFullText.trim()
                    const captionFullText = captionText.trimStart()

                    if (captionFullText.startsWith(originalText)) {
                        // Case 3A: same caption, license was added — replace without wrapping
                        originalCaptions.forEach(function (el) {
                            el.classList.add('media-license__figcaption')
                            el.innerHTML = caption
                        })
                    } else {
                        // Case 3B: genuinely different captions — keep block caption, append only license info
                        const captionDiv = document.createElement('div')
                        captionDiv.innerHTML = caption
                        // Strip the attachment caption: remove the caption span (plugin template)
                        // and any root-level text nodes (theme template)
                        captionDiv
                            .querySelectorAll('.media-license__caption')
                            .forEach(function (el) {
                                el.remove()
                            })
                        Array.from(captionDiv.childNodes).forEach(function (node) {
                            if (node.nodeType === 3) node.remove()
                        })
                        const licenseHtml = captionDiv.innerHTML

                        if (licenseHtml.trim().length > 0) {
                            // The block keeps its own caption, so the credit needs a
                            // separator of its own here - the template's is a root text
                            // node and was just stripped above.
                            originalCaptions.forEach(function (el) {
                                el.classList.add('media-license__figcaption')
                                el.insertAdjacentHTML(
                                    'beforeend',
                                    '<span class="media-license__separator"> | </span>' +
                                        licenseHtml
                                )
                            })
                        }
                    }
                }
            }

            if (figure.querySelector('.media-license__local-figcaption')) {
                figure.classList.add('has-local-caption')
            }
            if (figure.querySelector('.media-license__caption')) {
                figure.classList.add('has-caption')
            }
        }
    }

    /**
     * The values jQuery's .data() used to read as false: a missing or empty
     * attribute, "false", "null" and "0". The plugin itself only ever writes "true".
     * @param value
     * @return {boolean}
     */
    function is_truthy_data(value) {
        return (
            typeof value === 'string' &&
            value !== '' &&
            value !== 'false' &&
            value !== 'null' &&
            Number(value) !== 0
        )
    }

    /**
     * the text content of an HTML string, without inserting it into the page
     * @param html
     * @return {string}
     */
    function html_to_text(html) {
        const div = document.createElement('div')
        div.innerHTML = html
        return div.textContent
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
            // ids[]=1&ids[]=2, as jQuery serialized the array
            var params = new URLSearchParams()
            _ids.forEach(function (id) {
                params.append('ids[]', id)
            })
            // resturl carries a query string of its own with plain permalinks
            // (?rest_route=/media_license/v1/captions)
            var url =
                api.resturl +
                (api.resturl.indexOf('?') === -1 ? '?' : '&') +
                params.toString()
            fetch(url, { credentials: 'same-origin' })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status + ' ' + response.statusText)
                    return response.json()
                })
                .then(function (result) {
                    promise.trigger(result)
                })
                .catch(function (error) {
                    console.error('ML', error)
                })
        }

        return promise
    }

    if (api.autoload) {
        // auto load license
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                api.load_licenses()
            })
        } else {
            api.load_licenses()
        }
    }
})(MediaLicense_API)
