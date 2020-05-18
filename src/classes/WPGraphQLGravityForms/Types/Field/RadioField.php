<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Radio button field.
 *
 * @see https://docs.gravityforms.com/gf_field_radio/
 */
class RadioField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'RadioField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'radio';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Radio field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\ChoicesProperty::get(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\EnableChoiceValueProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\NoDuplicatesProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    'enableOtherChoice' => [
                        'type'        => 'Boolean',
                        'description' => __( 'Indicates whether the \'Enable "other" choice\' option is checked in the editor.', 'wp-graphql-gravity-forms' ),
                    ],
                ]
            ),
        ] );
    }
}
