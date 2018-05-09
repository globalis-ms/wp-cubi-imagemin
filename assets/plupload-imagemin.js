jQuery(document).ready(function($){

	if (typeof WPCubi_ImageMin === "undefined") {
		return;
	}

	var field_disabled = WPCubi_ImageMin.field_disabled;

	if (typeof uploader !== "undefined") {
		var target = uploader.settings.multipart_params;
	} else if (typeof wp !== "undefined" && typeof wp.Uploader !== "undefined") {
		var target = wp.Uploader.defaults.multipart_params;

		$.extend(wp.Uploader.prototype, {
			added: function(bla1) {
				this.param(field_disabled, wp.Uploader.defaults.multipart_params[field_disabled]);
			}
		});

		var old_refresh = wp.media.view.UploaderWindow.prototype.refresh;
		$.extend(wp.media.view.UploaderWindow.prototype, {
			refresh: function() {
				old_refresh.apply(this);
				$('#wp_cubi_imagemin').prop('checked', wp.Uploader.defaults.multipart_params[field_disabled]);
			}
		});
	} else {
		return;
	}
	
	target[field_disabled] = 0;

	$('body').on('change', '#wp_cubi_imagemin', function(){
		if ($(this).prop('checked')) {
			target[field_disabled] = 1;
		} else {
			target[field_disabled] = 0;
		}
	});
});
