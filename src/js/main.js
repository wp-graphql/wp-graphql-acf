$j = jQuery.noConflict();

$j(document).ready(function () {
	initContentNodeCheckbox();
	initTermNodeCheckbox();

	function initContentNodeCheckbox() {
		// toggle all the post_type checkboxes
		let $contentNodeCheckbox = $j('input[value="content_node"]');
		let $postTypeCheckboxes = $j('input[value^="post_type__"]');

		// add a line before the contentNode row
		$postTypeCheckboxes.before("&nbsp;&nbsp;");

		// if content_node checkbox changed, toggle post_type checkboxes
		$contentNodeCheckbox.change(function () {
			$postTypeCheckboxes.prop("checked", $j(this).is(":checked"));
		});

		// if post_type checkbox changed, update content_node checkbox
		$postTypeCheckboxes.change(function () {
			// uncheck content_node checkbox if one of post_type checkbox is unchecked
			if (
				!$j(this).is(":checked") &&
				$contentNodeCheckbox.is(":checked")
			) {
				$contentNodeCheckbox.prop("checked", false);
			}

			// if all the post_type checkboxes are checked, check content_node checkbox
			if ($j('input[value^="post_type__"]:not(:checked)').length == 0) {
				$contentNodeCheckbox.prop("checked", true);
			}
		});
	}

	function initTermNodeCheckbox() {
		// toggle all the taxonomy checkboxes
		let $termNodeCheckbox = $j('input[value="term_node"]');
		let $taxonomyCheckboxes = $j('input[value^="taxonomy__"]');

		$taxonomyCheckboxes.before("&nbsp;&nbsp;");

		// if term_node checkbox changed, toggle taxonomy checkboxes
		$termNodeCheckbox.change(function () {
			$taxonomyCheckboxes.prop("checked", $j(this).is(":checked"));
		});

		// if taxonomy checkbox changed, update term_node checkbox
		$taxonomyCheckboxes.change(function () {
			// uncheck term_node checkbox if one of taxonomy checkbox is unchecked
			if (!$j(this).is(":checked") && $termNodeCheckbox.is(":checked")) {
				$termNodeCheckbox.prop("checked", false);
			}

			// if all the taxonomy checkboxes are checked, check term_node checkbox
			if ($j('input[value^="taxonomy__"]:not(:checked)').length == 0) {
				$termNodeCheckbox.prop("checked", true);
			}
		});
	}
});
