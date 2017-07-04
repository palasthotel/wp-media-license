/**
 * Created by edward on 31.05.17.
 */
'use strict';

(function($, api){

	/**
	 * load license information to core image elements
	 */
	api.load_licenses = function(){
		var map = {};
		var ids = [];
		$("img").each(function(i, img){

			var id = api.get_image_id(img);
			if(id){
				if(!$(img).closest(".wp-caption").length){
					ids.push(id);
					if(typeof map[id] === typeof undefined){
						map[id] = [];
					}
					map[id].push(img);
				}
			}
		});

		api.get_licenses(ids).then(_got_licenses);

		/**
		 * build dom from results
		 * @param result
		 * @private
		 */
		function _got_licenses(result){

			if(result.error){
				console.error(result);
				return;
			}


			if(typeof result.captions === typeof []){

				for(var id in result.captions){
					if(!result.captions.hasOwnProperty(id)) continue;
					for(var i in map[id]){
						process_image(map[id][i], result.captions[id]);
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
		function process_image(element, caption){
			var $img = $(element);

			var $figure = $("<figure></figure>")
			.addClass("wp-caption media-license__figure");

			if($img.hasClass("alignright")){
				$figure.addClass("alignright");
				$img.removeClass("alignright");
			}
			if($img.hasClass("alignleft")){
				$figure.addClass("alignleft");
				$img.removeClass("alignleft");
			}
			$img.wrap($figure);

			var $caption = $("<figcaption>"+caption+"</figcaption>").addClass("wp-caption-text media-license__figcaption");
			$img.after($caption);
		}

	};



	/**
	 * get attachment id from wp-image-{id} class
	 * @param img_element
	 * @return {*}
	 */
	api.get_image_id = function(img_element){
		var matches = null;
		if( matches = /wp-image-([0-9]+)/g.exec(img_element.className) ){
			return parseInt(matches[1]);
		}
		return false;
	};

	/**
	 * load captions for attachment ids
	 * @param attachment_ids
	 * @return {{then, trigger}} register a callback with then method. could be called several times.
	 */
	api.get_licenses = function(attachment_ids){

		var promise = function(){
			var _cbs = [];
			function _then(cb){
				_cbs.push(cb);
			}
			function _trigger(result){
				for(var i = 0; i < _cbs.length; i++){
					_cbs[i](result);
				}
			}
			return {
				then: _then,
				trigger: _trigger,
			}
		}();


		while(attachment_ids.length){
			// get 10 attachment captions per call
			var _ids = attachment_ids.splice(0,10);
			$.ajax({
				method: "GET",
				url: api.ajaxurl,
				data: {
					action: api.params.action,
					ids: _ids,
				},
			}).done(function(result){
				promise.trigger(result);
			});
		}

		return promise;
	};

	if(api.autoload){
		// auto load license
		$(function(){
			api.load_licenses();
		});
	}


})(jQuery, MediaLicense_API);