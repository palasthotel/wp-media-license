'use strict';

(function (api) {

	/**
	 * load license information to core image elements
	 */
	api.load_licenses = function () {
		const map = {};
		const ids = [];

		document.querySelectorAll("img").forEach((img)=>{
			const id = api.get_image_id(img);
			if (id) {
				// always check caption
				ids.push(id);
				if (typeof map[id] === typeof undefined) {
					map[id] = [];
				}
				map[id].push(img);
			}
		});

		api.get_licenses(ids).then(_got_licenses);

		/**
		 * build dom from results
		 * @param captions
		 * @private
		 */
		function _got_licenses(captions) {

			for (const id in captions) {
				if (!captions.hasOwnProperty(id)){
					continue;
				}
				for (const i in map[id]) {
					if (!map[id].hasOwnProperty(i)) {
						continue;
					}

					const caption = captions[id];
					if (caption.length > 0) {
						const element = map[id][i];
						process_image(element, caption);
					}
				}
			}

		}

		/**
		 * process image element with caption
		 * @param {Element} img
		 * @param caption
		 */
		function process_image(img, caption) {

			// check parent -
			let figure;
			if(img.parentNode.nodeName === "FIGURE"){
				figure = img.parentNode;
			} else if(img.parentNode.nodeName === "A" && img.parentNode.parentNode.nodeName ==="FIGURE"){
				figure = img.parentNode.parentNode;
			} else {
				figure = document.createElement("figure");
				img.parentNode.insertBefore(figure, img);
				figure.append(img);
			}

			figure.classList.add("media-license__figure");
			// ✅ $figure now exists

			// take over alignment
			if (img.classList.contains("alignright")) {
				figure.classList.add("alignright");
				img.classList.remove("alignright");
			}
			if (img.classList.contains("alignleft")) {
				figure.classList.add("alignleft");
				img.classList.remove("alignleft");
			}
			if (img.classList.contains("aligncenter")) {
				figure.classList.add("aligncenter");
				img.classList.remove("aligncenter");
			}

			const originalCaption = figure.querySelector("figcaption");

			const captionHelper = document.createElement("div");
			captionHelper.innerHTML = caption;

			if (!originalCaption) {
				const captionElement = document.createElement("figcaption");
				captionElement.classList.add("wp-caption-text","media-license__figcaption");
				captionElement.innerHTML = caption;
				figure.appendChild(captionElement);
			} else if(
				originalCaption.innerText !== captionHelper.innerText
			){

				const wrappedOriginal = document.createElement("span");
				wrappedOriginal.innerHTML = originalCaption.innerHTML;
				wrappedOriginal.classList.add("media-license__local-figcaption");

				originalCaption.classList.add("media-license__figcaption");
				originalCaption.innerHTML = "";
				originalCaption.append(wrappedOriginal);
				originalCaption.innerHTML = originalCaption.innerHTML + caption;

				const testOriginal = figure.querySelector(".media-license__local-figcaption");
				const testAppended = figure.querySelector(".media-license__caption");
				if(
					testOriginal && testAppended && testOriginal.innerText === testAppended.innerText
				){
					figure.classList.add("has-duplicate-captions");
				}
			}

			if(figure.querySelector(".media-license__figcaption")){
				figure.classList.add("has-caption");
			}

			if(figure.querySelector(".media-license__local-figcaption")){
				figure.classList.add("has-local-caption");
			}


		}

	};


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
		const chunks = [];
		while (attachment_ids.length) {
			// get 10 attachment captions per call
			chunks.push(attachment_ids.splice(0, 10));
		}
		return Promise.all(chunks.map(ids=>{
			return fetch(api.resturl+"?"+ids.map(id=> "ids[]="+id).join("&"));
		})).then(responses => {
			return Promise.all(responses.map(response => response.json()));
		}).then(results => {
			const captions = {};
			for (const result of results) {
				if(result.error){
					console.error("Got error in media license captions response", result);
					continue;
				}
				for (const postId in result.captions) {
					captions[postId] = result.captions[postId];
				}
			}
			return captions;
		});
	};

	if (api.autoload) {
		document.addEventListener("DOMContentLoaded", function() {
			api.load_licenses();
		});
	}


})(MediaLicense_API);
