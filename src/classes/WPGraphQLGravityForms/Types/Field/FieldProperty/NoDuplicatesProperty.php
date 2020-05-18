<?php

namespace WPGraphQLGravityForms\Types\Field\FieldProperty;

use WPGraphQLGravityForms\Interfaces\FieldProperty;

abstract class NoDuplicatesProperty implements FieldProperty {
    /**
     * Get 'noDuplicates' property.
     *
     * Applies to: hidden, text, website, phone, number, date, time, textarea,
     * select, radio, email, post_custom_field
     */
    public static function get() : array {
        return [
            'noDuplicates' => [
                'type'        => 'Boolean',
                'description' => __('Determines if the field allows duplicate submissions. 1 to prevent users from submitting the same value more than once, 0 to allow duplicate values.', 'wp-graphql-gravity-forms'),
            ],
        ];
    }
}
