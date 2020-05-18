<?php

namespace WPGraphQLGravityForms\Types\Button;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;
use WPGraphQLGravityForms\Types\ConditionalLogic\ConditionalLogic;

/**
 *  Button.
 *
 * @see https://docs.gravityforms.com/button/
 */
class Button implements Hookable, Type {
    const TYPE = 'Button';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms button.', 'wp-graphql-gravity-forms' ),
            'fields' => [
                // @TODO: Convert to an enum.
                'type'   => [
                    'type'        => 'String',
                    'description' => __( 'Specifies the type of button to be displayed. Possible values: text, image.', 'wp-graphql-gravity-forms' ),
                ],
                'text' => [
                    'type'        => 'String',
                    'description' => __( 'Contains the button text. Only applicable when type is set to text.', 'wp-graphql-gravity-forms' ),
                ],
                'imageUrl' => [
                    'type'        => 'String',
                    'description' => __( 'Contains the URL for the image button. Only applicable when type is set to image.', 'wp-graphql-gravity-forms' ),
                ],
                'conditionalLogic' => [
                    'type'        => ConditionalLogic::TYPE,
                    'description' => __( 'Controls when the form button should be visible based on values selected on the form.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
