<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

/**
 * Enable Enhanced UI field property.
 *
 * @see https://docs.gravityforms.com/field-object/#other
 */
abstract class EnableEnhancedUiProperty implements FieldProperty {
    /**
     * Get 'enableEnhancedUI' property.
     *
     * Applies to: select, multiselect
     */
    public static function get() : array {
        return [
            'enableEnhancedUI' => [
                'type'        => 'Boolean',
                'description' => __('When set to true, the "Chosen" jQuery script will be applied to this field, enabling search capabilities to Drop Down fields and a more user-friendly interface for Multi Select fields.', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
