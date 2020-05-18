<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class ErrorMessageProperty implements FieldProperty {
    /**
     * Get 'errorMessage' property.
     *
     * Applies to: All fields except html, section and hidden
     */
    public static function get() : array {
        return [
            'errorMessage' => [
                'type'        => 'String',
                'description' => __('Contains the message that is displayed for fields that fail validation.', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
