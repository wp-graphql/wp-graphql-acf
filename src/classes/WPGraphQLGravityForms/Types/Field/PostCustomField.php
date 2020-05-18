<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Post Custom Field.
 *
 * @see https://docs.gravityforms.com/post-custom/
 */
class PostCustomField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'PostCustomField';

    /**
     * Type registered in WPGraphQL.
     */
    const GF_TYPE = 'post_custom_field';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Post Custom Field field.', 'wp-graphql-gravity-forms' ),
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
                    'postCustomFieldName' => [
                        'type'        => 'String',
                        'description' => __('The name of the Post Custom Field that the submitted value should be assigned to.', 'wp-graphql-gravity-forms'),
                    ],
                    'inputType' => [
                        'type'        => 'String',
                        'description' => __('Contains a field type and allows a field type to be displayed as another field type. A good example is the Post Custom Field, that can be displayed as various different types of fields.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
