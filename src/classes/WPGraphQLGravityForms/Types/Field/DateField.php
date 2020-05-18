<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Date field.
 *
 * @see https://docs.gravityforms.com/gf_field_date/
 */
class DateField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'DateField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'date';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Date field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DefaultValueProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\PlaceholderProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    /**
                     * Possible values: Possible values: calendar, custom, none
                     */
                    'calendarIconType' => [
                        'type'        => 'String',
                        'description' => __('Determines how the date field displays it’s calendar icon.', 'wp-graphql-gravity-forms'),
                    ],
                    'calendarIconUrl' => [
                        'type'        => 'String',
                        'description' => __('Contains the URL to the custom calendar icon. Only applicable when calendarIconType is set to custom.', 'wp-graphql-gravity-forms'),
                    ],
                    /**
                     * Possible values: mdy, dmy
                     */
                    'dateFormat' => [
                        'type'        => 'String',
                        'description' => __('Determines how the date is displayed.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
