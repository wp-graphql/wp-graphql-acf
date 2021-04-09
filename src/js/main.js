$j = jQuery.noConflict();

$j(document).ready(function () {

	/**
	 * Set the visibility of the GraphQL Fields based on the `show_in_graphql`
	 * field state.
	 */
	function setGraphqlFieldVisibility() {

		var showInGraphQLCheckbox = $j('#acf_field_group-show_in_graphql');
		var graphqlFields = $j('#wpgraphql-acf-meta-box .acf-field');

		graphqlFields.each( function( i, el ) {
			if ( $j( this ).attr('data-name') !== 'show_in_graphql' ) {
				if ( ! showInGraphQLCheckbox.is(':checked') ) {
					$j(this).hide();
				} else {
					$j(this).show();
				}
			}
		});

		showInGraphQLCheckbox.on('change', function() {
			setGraphqlFieldVisibility();
		})

	}

	function toggleFieldRequirement() {

		$j('#acf_field_group-show_in_graphql').on('change', function () {
			var graphqlFieldNameWrap = $j('.acf-field[data-name="graphql_field_name"]'),
				graphqlLabel = graphqlFieldNameWrap.find('label'),
				graphqlInput = $j('#acf_field_group-graphql_field_name');

			if ($j(this).is(':checked')) {

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

	}

	/**
	 * Listen to state changes for checkboxes for Interfaces and the checkboxes for
	 * Possible Types of the interfaces
	 */
	function initInterfaceCheckboxes() {

		// Find all the checkboxes for Interface types
		$j('span[data-interface]').each( function( i, el) {

			// Get the interface name
			let interfaceName = $j(el).data('interface');

			// Get the checkbox for the interface
			let interfaceCheckbox = $j('input[value="' + interfaceName + '"]');

			// Find all checkboxes that implement the interface
			let possibleTypesCheckboxes = $j('span[data-implements="' + interfaceName + '"]').siblings('input[type="checkbox"]');

			// Prepend some space before to nest the Types beneath the Interface
			possibleTypesCheckboxes.before( "&nbsp;&nbsp;" );

			// Listen for changes on the Interface checkbox
			interfaceCheckbox.change(function() {
				possibleTypesCheckboxes.prop('checked', $j(this).is(":checked"));
			})

			// Listen for changes to the checkboxes that implement the Interface
			possibleTypesCheckboxes.change(function () {

				// Set the checked state of the Interface checkbox
				if ( ! $j(this).is(":checked") && interfaceCheckbox.is(":checked") ) {
					interfaceCheckbox.prop("checked", false);
				}

				// Set the state of the Implementing checkboxes
				if ($j(possibleTypesCheckboxes).not(":checked").length === 0) {
					interfaceCheckbox.prop("checked", true);
				}

			})

		});

	}

	/**
	 * JavaScript version of the PHP lcfirst
	 *
	 * @param str
	 * @returns {string}
	 */
	function lcfirst( str ) {
		str += ''
		const f = str.charAt(0)
			.toLowerCase()
		return f + str.substr(1)
	}

	/**
	 * JavaScript version of the PHP ucwords
	 *
	 * @param str
	 * @returns {string}
	 */
	function ucwords (str) {
		return str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
			return letter.toUpperCase();
		})
	}

	/**
	 * Based on the WPGraphQL format_field_name function
	 *
	 * See: https://github.com/wp-graphql/wp-graphql/blob/cc0b383259383898c3a1bebe65adf1140290b37e/src/Utils/Utils.php#L85-L100
	 */
	function formatFieldName( fieldName ) {

		fieldName.replace( '[^a-zA-Z0-9 -]', '_' );
		fieldName = lcfirst( fieldName );
		fieldName = lcfirst( fieldName.split( '_' ).join( ' ' ) );
		fieldName = lcfirst( fieldName.split( '-' ).join( ' ' ) );
		fieldName = ucwords( fieldName );
		fieldName = lcfirst( fieldName.split( ' ' ).join( '' ) );
		return fieldName;

	}

	/**
	 * Set the GraphQL Field Name value based on the Field Group Title
	 * if the graphql_field_name has not already been set.
	 */
	function setGraphqlFieldName() {
		var graphqlFieldNameField = $j('#acf_field_group-graphql_field_name');
		var fieldGroupTitle = $j('#titlediv #title');
		if ( '' === graphqlFieldNameField.val() ) {
			graphqlFieldNameField.val( formatFieldName( fieldGroupTitle.val() ) );
		}
		fieldGroupTitle.on('change', function() {
			setGraphqlFieldName();
		});
	}


	// Initialize the functionality to track the state of the Interface checkboxes.
	initInterfaceCheckboxes();
	toggleFieldRequirement();
	setGraphqlFieldVisibility();
	setGraphqlFieldName();

});

(function($, undefined){

	var GraphqlLocationManager = new acf.Model({
		id: 'graphqlLocationManager',
		wait: 'ready',
		events: {
			'click .add-location-rule':			'onClickAddRule',
			'click .add-location-group':		'onClickAddGroup',
			'click .remove-location-rule':		'onClickRemoveRule',
			'change .refresh-location-rule':	'onChangeRemoveRule',
			'change .rule-groups .operator select': 'onChangeRemoveRule',
			'change .rule-groups .value select': 'onChangeRemoveRule',
		},
		initialize: function(){
			this.$el = $('#acf-field-group-locations');
			console.log( 'graphql-location-manager-initialized');
			this.getGraphqlTypes();
		},

		onClickAddRule: function( e, $el ){
			console.log( 'onClickAddRule...' );
			this.getGraphqlTypes();
		},

		onClickRemoveRule: function( e, $el ){
			console.log( 'onClickRemoveRule...' );
			this.getGraphqlTypes();
		},

		onChangeRemoveRule: function( e, $el ){
			console.log( 'onChangeRemoveRule...' );
			setTimeout( function() {
				GraphqlLocationManager.getGraphqlTypes();
			}, 1000);

		},

		onClickAddGroup: function( e, $el ){
			console.log( 'onClickAddGroup...' );
			this.getGraphqlTypes();
		},

		getGraphqlTypes: function(){
			this.getGraphqlTypesFromLocationRules();
		},
		getGraphqlTypesFromLocationRules: function() {

			var form = $('#post :input');
			var serialized = form.serialize();
			$.post( ajaxurl, { action: 'get_acf_field_group_graphql_types', data: serialized }, function(res) {
				console.log(res);
			});

			// var locationRules = $('#post :input[name^="acf_field_group[location]"]');
			// console.log(locationRules);
			// locationRules.on('change', function() {
			// 	console.log( 'rule changed...' );
			// 	GraphqlLocationManager.getGraphqlTypes();
			// });

		}
	})

})(jQuery);
