<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class IsRequiredProperty implements FieldProperty {
    /**
     * Get 'isRequired' property.
     *
     * Applies to: All fields except section, html and captcha
     */
    public static function get() : array {
        return [
            'isRequired' => [
                'type'        => 'Boolean',
                'description' => __('Determines if the field requires the user to enter a value. 1 marks the field as required, 0 marks the field as not required. Fields marked as required will prevent the form from being submitted if the user has not entered a value in it.', 'wp-graphql-gravity-forms'),
            ], 
        ];
    }
}
