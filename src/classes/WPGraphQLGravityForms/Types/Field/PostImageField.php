<?php

namespace WPGraphQLGravityForms\Types\Field;

use WPGraphQLGravityForms\Types\Field\FieldProperty;

/**
 * Post Image field.
 *
 * @see https://docs.gravityforms.com/post-image/
 */
class PostImageField extends Field {
    /**
     * Type registered in WPGraphQL.
     */
    const TYPE = 'PostImageField';

    /**
     * Type registered in Gravity Forms.
     */
    const GF_TYPE = 'post_image';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms Post Image field.', 'wp-graphql-gravity-forms' ),
            'fields'      => array_merge(
                $this->get_global_properties(),
                FieldProperty\DescriptionProperty::get(),
                FieldProperty\ErrorMessageProperty::get(),
                FieldProperty\InputNameProperty::get(),
                FieldProperty\IsRequiredProperty::get(),
                FieldProperty\SizeProperty::get(),
                [
                    'displayCaption' => [
                        'type'        => 'Boolean',
                        'description' => __('Controls the visibility of the caption metadata for Post Image fields. 1 will display the caption field, 0 will hide it.', 'wp-graphql-gravity-forms'),
                    ],
                    'displayDescription' => [
                        'type'        => 'Boolean',
                        'description' => __('Controls the visibility of the description metadata for Post Image fields. 1 will display the description field, 0 will hide it.', 'wp-graphql-gravity-forms'),
                    ],
                    'displayTitle' => [
                        'type'        => 'Boolean',
                        'description' => __('Controls the visibility of the title metadata for Post Image fields. 1 will display the title field, 0 will hide it.', 'wp-graphql-gravity-forms'),
                    ],
                ]
            ),
        ] );
    }
}
