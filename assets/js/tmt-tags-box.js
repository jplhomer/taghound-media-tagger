/* This is a modified version of WP's native Tags Box script: wp-admin/js/tags-box.js */

/* jshint curly: false, eqeqeq: false */
/* global ajaxurl */

var IatTagBox, array_unique_noempty;

( function( $ ) {
	// Return an array with any duplicate, whitespace or empty values removed
	array_unique_noempty = function( array ) {
		var out = [];

		$.each( array, function( key, val ) {
			val = $.trim( val );

			if ( val && $.inArray( val, out ) === -1 ) {
				out.push( val );
			}
		} );

		return out;
	};

	IatTagBox = {
		clean : function(tags) {
			var comma = window.tagsBoxL10n.tagDelimiter;
			if ( ',' !== comma )
				tags = tags.replace(new RegExp(comma, 'g'), ',');
			tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
			if ( ',' !== comma )
				tags = tags.replace(/,/g, comma);
			return tags;
		},

		parseTags : function(el) {
			var id = el.id,
				num = id.split('-check-num-')[1],
				taxbox = $(el).closest('.tagsdiv'),
				thetags = taxbox.find('.the-tags'),
				comma = window.tagsBoxL10n.tagDelimiter,
				current_tags = thetags.val().split( comma ),
				new_tags = [];

			delete current_tags[num];

			$.each( current_tags, function( key, val ) {
				val = $.trim( val );
				if ( val ) {
					new_tags.push( val );
				}
			});

			thetags.val( this.clean( new_tags.join( comma ) ) );
			thetags.trigger('change');

			this.quickClicks( taxbox );
			return false;
		},

		quickClicks : function( el ) {
			var thetags = $('.the-tags', el),
				tagchecklist = $('.tagchecklist', el),
				id = $(el).attr('id'),
				current_tags, disabled;

			if ( ! thetags.length )
				return;

			disabled = thetags.prop('disabled');

			current_tags = thetags.val().split( window.tagsBoxL10n.tagDelimiter );
			tagchecklist.empty();

			$.each( current_tags, function( key, val ) {
				var span, xbutton;

				val = $.trim( val );

				if ( ! val )
					return;

				// Create a new span, and ensure the text is properly escaped.
				span = $('<span />').text( val );

				// If tags editing isn't disabled, create the X button.
				if ( ! disabled ) {
					/*
					 * Build the X buttons, hide the X icon with aria-hidden and
					 * use visually hidden text for screen readers.
					 */
					xbutton = $( '<button type="button" id="' + id + '-check-num-' + key + '" class="ntdelbutton">' +
						'<span class="remove-tag-icon" aria-hidden="true"></span>' +
						'<span class="screen-reader-text">' + window.tagsSuggestL10n.removeTerm + ' ' + val + '</span>' +
						'</button>' );

					xbutton.on( 'click keypress', function( e ) {
						// On click or when using the Enter/Spacebar keys.
						if ( 'click' === e.type || 13 === e.keyCode || 32 === e.keyCode ) {
							/*
							 * When using the keyboard, move focus back to the
							 * add new tag field. Note: when releasing the pressed
							 * key this will fire the `keyup` event on the input.
							 */
							if ( 13 === e.keyCode || 32 === e.keyCode ) {
 								$( this ).closest( '.tagsdiv' ).find( 'input.newtag' ).focus();
 							}

							IatTagBox.userAction = 'remove';
							IatTagBox.parseTags( this );
						}
					});

					span.prepend( '&nbsp;' ).prepend( xbutton );
				}

				// Append the span to the tag list.
				tagchecklist.append( span );
			});
			// The buttons list is built now, give feedback to screen reader users.
			IatTagBox.screenReadersMessage();
		},

		flushTags : function( el, a, f ) {
			var tagsval, newtags, text,
				tags = $( '.the-tags', el ),
				newtag = $( 'input.newtag', el ),
				comma = window.tagsBoxL10n.tagDelimiter;

			a = a || false;

			text = a ? $(a).text() : newtag.val();

			if ( 'undefined' == typeof( text ) ) {
				return false;
			}

			tagsval = tags.val();
			newtags = tagsval ? tagsval + comma + text : text;

			newtags = this.clean( newtags );
			newtags = array_unique_noempty( newtags.split( comma ) ).join( comma );
			tags.val( newtags );
			tags.trigger('change');
			this.quickClicks( el );

			if ( ! a )
				newtag.val('');
			if ( 'undefined' == typeof( f ) ) {
				newtag.focus();
				window.iatAddedTag = true;
			}

			return false;
		},

		get : function( id ) {
			var tax = id.substr( id.indexOf('-') + 1 );

			$.post( ajaxurl, { 'action': 'get-tagcloud', 'tax': tax }, function( r, stat ) {
				if ( 0 === r || 'success' != stat ) {
					return;
				}

				r = $( '<p id="tagcloud-' + tax + '" class="the-tagcloud">' + r + '</p>' );

				$( 'a', r ).click( function() {
					IatTagBox.flushTags( $( '#' + tax ), this );
					return false;
				});

				$( '#' + id ).after( r );
			});
		},

		/**
		 * Track the user's last action.
		 *
		 * @since 4.7.0
		 */
		userAction: '',

		/**
		 * Dispatch an audible message to screen readers.
		 *
		 * @since 4.7.0
		 */
		screenReadersMessage: function() {
			var message;

			switch ( this.userAction ) {
				case 'remove':
					message = window.tagsSuggestL10n.termRemoved;
					break;

				case 'add':
					message = window.tagsSuggestL10n.termAdded;
					break;

				default:
					return;
			}

			window.wp.a11y.speak( message, 'assertive' );
		},

		init : function( $el ) {
			var t = this, ajaxtag = $('div.ajaxtag');

			$el.find('.tagsdiv').each( function() {
				IatTagBox.quickClicks(this);
			});

			$el.find('.tagadd', ajaxtag).click(function(){
				t.flushTags( $(this).closest('.tagsdiv') );
			});

			$el.find('input.newtag', ajaxtag).keyup(function(e){
				if ( 13 == e.which ) {
					IatTagBox.flushTags( $(this).closest('.tagsdiv') );
					return false;
				}
			}).keypress(function(e){
				if ( 13 == e.which ) {
					e.preventDefault();
					return false;
				}
			}).each( function() {
				var tax = $(this).closest('div.tagsdiv').attr('id');
				$(this).suggest(
					ajaxurl + '?action=ajax-tag-search&tax=' + tax,
					{ delay: 500, minchars: 2, multiple: true, multipleSep: window.tagsBoxL10n.tagDelimiter }
				);
			});

			// save tags on post save/publish
			$el.find('#post').submit(function(){
				$el.find('div.tagsdiv').each( function() {
					IatTagBox.flushTags(this, false, 1);
				});
			});

			// tag cloud
			$el.find('.tagcloud-link').click(function(){
				IatTagBox.get( $(this).attr('id') );
				$(this).unbind().click(function(){
					$(this).siblings('.the-tagcloud').toggle();
					return false;
				});
				return false;
			});
		}
	};
}( jQuery ));
