/**
 * Created by edward on 31.05.17.
 */
'use strict';

// IIFE Immediately Invoked Function Expression is an anonymus function that is executed immediately 
(function ($, api) {

	/**
	 * load license information to core image elements
	 */
	api.load_licenses = function () {
		var map = {};
		var ids = [];
		$("img").each(function (i, img) {

			var id = api.get_image_id(img);
			if (id) {
				// always check caption
				ids.push(id);
				if (typeof map[id] === typeof undefined) {
					map[id] = [];
				}
				map[id].push(img);
			}
		});

        // get_licenses runs immediately since it is an IIFE
        // _got_licenses is the registered callback that runs for every result of the get_licenses calls
        // since get_licenses batches ids of 10
         
        // fix duplication problem by removing duplicats
        const sanitizedIds = Array.from(new Set(ids));
		api.get_licenses(sanitizedIds).then(_got_licenses);

		/**
		 * build dom from results
		 * @param result
		 * @private
		 */
		function _got_licenses(result) {

			if (result.error) {
				console.error(result);
				return;
			}


			if (typeof result.captions === typeof []) {

				for (var id in result.captions) {
					if (!result.captions.hasOwnProperty(id)) continue;
                    // this loop processes all img that share the same id
					for (var i in map[id]) {
						if (!map[id].hasOwnProperty(i)) continue;

						var caption = result.captions[id];
						if (caption.length > 0) {
							var element = map[id][i];
							process_image(element, caption);
						}
					}
				}

			} else {
				console.error("captions was no array", result);
			}
		}

		/**
		 * process image element with caption
		 * @param element
		 * @param caption
		 */
		function process_image(element, caption) {

			var $img = $(element);
            // jquery converts the data-attribute 'data-media-license-block-flag' to 'mediaLicenseBlockFlag'
            if ($img.data("mediaLicenseBlockUseDataAttribute")) {
                add_media_license_as_data_attribute(element, caption);
                return;
            }
			var $figure = $("<figure></figure>");

			// check parent -
			if($img.parent("figure").length === 1){
				$figure = $img.parent();
			} else if($img.parent("a").length === 1 && $img.parent().parent("figure").length === 1){
				$figure = $img.parent().parent();
			} else {
				$img.wrap($figure);
			}

			$figure.addClass("media-license__figure")
			// ✅ $figure now exists

			// take over alignment
			if ($img.hasClass("alignright")) {
				$figure.addClass("alignright");
				$img.removeClass("alignright");
			}
			if ($img.hasClass("alignleft")) {
				$figure.addClass("alignleft");
				$img.removeClass("alignleft");
			}
			if ($img.hasClass("aligncenter")) {
				$figure.addClass("aligncenter");
				$img.removeClass("aligncenter");
			}

			const $originalCaption = $figure.find("figcaption");

			console.debug("ML",$originalCaption);

			if ($figure.find("figcaption").length === 0) {
				console.debug("ML", "figcaption  not found");
				var $caption = $("<figcaption>" + caption + "</figcaption>").addClass("wp-caption-text media-license__figcaption");
				// image is wrapped with link
				if ($img.parent("a").length === 1) {
					$img.next("figure").append($caption);
				} else {
					$img.after($caption);
				}
			} else if($originalCaption.text() !== $(caption).text()){
				console.debug("ML", "figcaption found but no equal!", $originalCaption.text(), $(caption).text());
				const $wrappedOriginal = $("<span>" + $originalCaption.html() + "</span>").addClass("media-license__local-figcaption")
				$originalCaption.addClass("media-license__figcaption")
					.empty()
					.append($wrappedOriginal)
					.append(caption);
			}

			if($figure.find(".media-license__local-figcaption").length > 0){
				$figure.addClass("has-local-caption");
			}
			if($figure.find(".media-license__caption").length > 0){
				$figure.addClass("has-caption")
			}

		}

	};

    function add_media_license_as_data_attribute(element, caption) {
        element.setAttribute('data-media-license-caption', caption); 
    }

	/**
	 * get attachment id from wp-image-{id} class
	 * @param img_element
	 * @return {*}
	 */
	api.get_image_id = function (img_element) {
		var matches = null;
		if (matches = /wp-image-([0-9]+)/g.exec(img_element.className)) {
			return parseInt(matches[1]);
		}
		return false;
	};

	/**
	 * load captions for attachment ids
	 * @param attachment_ids
	 * @return {{then, trigger}} register a callback with then method. could be called several times.
	 */
	api.get_licenses = function (attachment_ids) {

        // this is also a immediately invoked function expression
		var promise = function () {
			var _cbs = [];

			function _then(cb) {
				_cbs.push(cb);
			}

			function _trigger(result) {
				for (var i = 0; i < _cbs.length; i++) {
					_cbs[i](result);
				}
			}

			return {
				then: _then,
				trigger: _trigger,
			}
		}();


		while (attachment_ids.length) {
			// get 10 attachment captions per call
			var _ids = attachment_ids.splice(0, 10);
			$.ajax({
				method: "GET",
				url: api.resturl,
				data: {
					ids: _ids,
				},
			}).done(function (result) {
				promise.trigger(result);
			});
		}

		return promise;
	};

	if (api.autoload) {
		// auto load license
		$(function () {
			api.load_licenses();
		});
	}


})(jQuery, MediaLicense_API);
