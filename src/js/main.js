$j = jQuery.noConflict();

$j(document).ready(function () {

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
					interfaceCheckbox.prop(":checked", false);
				}

				// Set the state of the Implementing checkboxes
				if ($j(possibleTypesCheckboxes).not(":checked").length === 0) {
					interfaceCheckbox.prop("checked", true);
				}

			})

		});

	}

	// Initialize the functionality to track the state of the Interface checkboxes.
	initInterfaceCheckboxes();

});
