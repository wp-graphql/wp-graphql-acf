<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

/**
 * Choices field property.
 *
 * @see https://docs.gravityforms.com/field-object/#basic-properties
 */
abstract class ChoicesProperty implements FieldProperty {
    /**
     * Get 'choices' property.
     *
     * Applies to: select, checkbox, radio, post_category
     */
    public static function get() : array {
        return [
            'choices' => [
                'type'        => [ 'list_of' => ChoiceProperty::TYPE ],
                'description' => __('Contains the available choices for the field. For instance, drop down items and checkbox items are configured with this property.', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
