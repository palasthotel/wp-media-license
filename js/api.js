/**
 * Created by edward on 31.05.17.
 */
(function($, api){

	api.load_licenses = function(){
		var map = {};
		var ids = [];
		$("img").each(function(i, img){

			var matches = null;
			if(matches = /wp-image-([0-9]+)/g.exec(img.className)){
				if(!$(img).closest(".wp-caption").length){
					var attachment_id = parseInt(matches[1]);
					ids.push(attachment_id);
					map[attachment_id] = img;
				}
			}
		});

		if(ids.length < 1) return;

		api.get_licenses(ids).done(function(result){

			if(result.error){
				console.error(result);
				return;
			}


			if(typeof result.captions === typeof []){

				for(var id in result.captions){
					if(!result.captions.hasOwnProperty(id)) continue;

					var $img = $(map[id]);

					var $figure = $("<figure></figure>")
						.addClass("wp-caption");

					if($img.hasClass("alignright")){
						$figure.addClass("alignright");
						$img.removeClass("alignright");
					}
					if($img.hasClass("alignleft")){
						$figure.addClass("alignleft");
						$img.removeClass("alignleft");
					}
					$img.wrap($figure);

					var $caption = $("<figcaption>"+result.captions[id]+"</figcaption>").addClass("wp-caption-text");
					$img.after($caption);
				}

			} else {
				console.error("captions was no array", result);
			}
		});

	};

	api.get_licenses = function(attachment_ids){
		return $.ajax({
			method: "POST",
			url: api.ajaxurl,
			data: {
				action: api.params.action,
				ids: attachment_ids,
			},
		});
	};


	if(api.autoload){
		// auto load license
		$(function(){
			api.load_licenses();
		});
	}


})(jQuery, MediaLicense_API);