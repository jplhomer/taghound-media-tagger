/* global jQuery, _, wp */
'use strict';

jQuery(function($) {
	var makeBulkTaggingRequest = function() {
		$.post(
			ajaxurl,
			{
				action: 'tmt_bulk_tag'
			},
			function(response) {
				if ( response.success ) {
					console.log(response.data);
				} else {
					console.log(response);
				}
			}
		);
	};

	/**
	 * Overrides WP's Attachment view functions to instantiate our own scripts
	 * and serialize the form in a custom way.
	 * @return {void}
	 */
	var setUpAttachmentOverrides = function() {
		_.extend( wp.media.view.AttachmentCompat.prototype, {
			render: function() {
				var compat = this.model.get('compat');
				if ( ! compat || ! compat.item ) {
					return;
				}

				this.views.detach();
				this.$el.html( compat.item );
				this.views.render();

				if ( window.IatTagBox ) {
					window.IatTagBox.init( this.$el );
				}

				if ( window.iatAddedTag == true ) {
					window.iatAddedTag = false;

					this.$el.find('input.newtag').focus();
				}

				return this;
			}
		});
	};

	/**
	 * Kick off bulk tagging
	 */
	var setUpBulkTagging = function() {
		var bulkTagSelector = '[data-bulk-tag-init]';

		if ( ! $(bulkTagSelector).length ) {
			return false;
		}

		$(bulkTagSelector).on('click', function(e) {
			e.preventDefault();
			makeBulkTaggingRequest();
		});
	};

	var init = function() {
		if ( typeof wp.media !== 'undefined' ) {
			setUpAttachmentOverrides();
		}
		setUpBulkTagging();
	}

	init();
});
