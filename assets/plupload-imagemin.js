jQuery(document).ready(function($){
	if (typeof uploader !== "undefined") {
		var target = uploader.settings.multipart_params;
	} else if (typeof wp !== "undefined" && typeof wp.Uploader !== "undefined") {
		var target = wp.Uploader.defaults.multipart_params;

		$.extend(wp.Uploader.prototype, {
			added: function(bla1) {
				this.param('_wp_cubi_imagemin_disabled', wp.Uploader.defaults.multipart_params._wp_cubi_imagemin_disabled);
			}
		});

		var old_refresh = wp.media.view.UploaderWindow.prototype.refresh;
		$.extend(wp.media.view.UploaderWindow.prototype, {
			refresh: function() {
				old_refresh.apply(this);
				$('#wp_cubi_imagemin').prop('checked', wp.Uploader.defaults.multipart_params._wp_cubi_imagemin_disabled);
			}
		});
	} else {
		return;
	}

	target._wp_cubi_imagemin_disabled = 0;

	$('body').on('change', '#wp_cubi_imagemin', function(){
		if ($(this).prop('checked')) {
			target._wp_cubi_imagemin_disabled = 1;
		} else {
			target._wp_cubi_imagemin_disabled = 0;
		}
	});
});
