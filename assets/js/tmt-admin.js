/* global jQuery, _, wp */
'use strict';

jQuery(function($) {
	var bulkTagSelector = '[data-bulk-tag-init]';
	var errorsSelector = '.tmt-errors';
	var statusSelector = '.tmt-status';
	var totalFailed = 0;

	var makeBulkTaggingRequest = function( results ) {
		// Clear inputs
		$(bulkTagSelector).attr('disabled', true);
		$(errorsSelector).html('');

		// Grab the starting number of untagged images from the UI
		var startingNumber = $('[data-starting-number]').text();

		var data = {
			action: 'tmt_bulk_tag',
			skip: []
		};

		if ( results ) {
			data.tagged = results.tagged;

			if ( results.skip ) {
				data.skip.concat(results.skip);
			}

			if ( results.failed ) {
				totalFailed += results.failed.length;
				data.skip.concat(results.failed.map(function(result) {
					return result['post_id'];
				}));
			}
		}

		$.post(
			ajaxurl,
			data,
			function(response) {
				if ( response.success ) {
					$(statusSelector).append('<p>' + response.data.results.tagged + ' of ' + startingNumber + ' images tagged...</p>');
					if ( response.data.results.continue ) {
						makeBulkTaggingRequest( response.data.results );
					} else {
						$(statusSelector).append('<p>Success! ' + response.data.results.tagged + ' of ' + startingNumber + ' images have been tagged.</p>');
						if ( totalFailed ) {
							$(statusSelector).append('<p>' + totalFailed + ' images could not be tagged.</p>');
						}
						console.log(response.data);
					}
				} else {
					var errorMessage = 'Error: ' + response.data.results.error_message + '<br>';
					// Compile all the result messages
					errorMessage += response.data.results.results.results.map(function(result) {
						return result.result.error;
					}).join('<br>');
					$(errorsSelector).html(errorMessage);
					$(bulkTagSelector).removeAttr('disabled');
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
		if ( ! $(bulkTagSelector).length ) {
			return false;
		}

		$(bulkTagSelector).on('click', function(e) {
			e.preventDefault();
			makeBulkTaggingRequest();
			$(statusSelector).append('<p>Beginning the tagging process. Please do not navigate away from this page...</p>');
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
