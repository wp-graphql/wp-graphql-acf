<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class InputsProperty implements FieldProperty {
    /**
     * Get 'inputs' property.
     *
     * Applies to: name, address
     */
    public static function get() : array {
        return [
            'inputs' => [
                'type'        => [ 'list_of' => InputProperty::TYPE ],
                'description' => __('For fields with multiple inputs (i.e. Name, Address), this property contains a list of inputs. This property also applies to the checkbox field as checkboxes are treated as multi-input fields (since each checkbox item is stored separately).', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
