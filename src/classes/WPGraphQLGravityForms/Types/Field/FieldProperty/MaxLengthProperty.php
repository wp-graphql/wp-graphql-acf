<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use GF_Field;
use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class MaxLengthProperty implements FieldProperty {
    /**
     * Get 'maxLength' property.
     */
    public static function get() : array {
        return [
            'maxLength' => [
                'type'        => 'Integer',
                'description' => __('Specifies the maximum number of characters allowed in a text or textarea (paragraph) field.', 'wp-graphql-gravity-forms'),
                'resolve' => function( GF_Field $field ) : int {
                    return (int) $field['maxLength'];
                },
            ],
        ];
    }
}
