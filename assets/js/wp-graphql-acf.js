/* global jquery */
'use strict';

(function ($) {
	$(document).ready(function () {

		/**
		 * Toggle “GraphQL Field Name” rqeuirement based on “Show in GraphQL” toggle.
		 */
		$('#acf_field_group-show_in_graphql').on('change', function () {
			var graphqlFieldNameWrap = $('.acf-field[data-name="graphql_field_name"]'),
				graphqlLabel = graphqlFieldNameWrap.find('label'),
				graphqlInput = $('#acf_field_group-graphql_field_name');

			if ($(this).is(':checked')) {

				// Add span.acf-required if necessary.
				if (graphqlFieldNameWrap.find('.acf-required').length === 0) {
					graphqlLabel.append('<span class="acf-required">*</span>');
				}

				// Toggle required attributes and visual features.
				graphqlFieldNameWrap.addClass('is-required');
				graphqlLabel.find('.acf-required').show();
				graphqlInput.attr('required', true);
			} else {
				graphqlFieldNameWrap.removeClass('is-required');
				graphqlLabel.find('.acf-required').hide();
				graphqlInput.attr('required', false);
			}

		});
	});
}(jQuery));
