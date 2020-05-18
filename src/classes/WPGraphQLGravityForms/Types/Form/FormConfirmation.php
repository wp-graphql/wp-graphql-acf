<?php

namespace WPGraphQLGravityForms\Types\Form;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 * Form confirmation.
 *
 * @see https://docs.gravityforms.com/confirmation/
 */
class FormConfirmation implements Hookable, Type {
    const TYPE = 'FormConfirmation';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Properties for all the email notifications which exist for a form.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'id' => [
                    'type'        => 'String',
                    'description' => __( 'ID.', 'wp-graphql-gravity-forms' ),
                ],
                'name' => [
                    'type'        => 'String',
                    'description' => __( 'Name.', 'wp-graphql-gravity-forms' ),
                ],
                'isDefault' => [
                    'type'        => 'Boolean',
                    'description' => __( 'Whether this is the default confirmation.', 'wp-graphql-gravity-forms' ),
                ],
                'type' => [
                    'type'        => 'String',
                    'description' => __( 'Determines the type of confirmation to be used. Possible values: message, page, redirect.', 'wp-graphql-gravity-forms' ),
                ],
                'message' => [
                    'type'        => 'String',
                    'description' => __( 'Contains the confirmation message that will be displayed. Only applicable when type is set to message.', 'wp-graphql-gravity-forms' ),
                ],
                'url' => [
                    'type'        => 'String',
                    'description' => __( 'Contains the URL that the browser will be redirected to. Only applicable when type is set to redirect.', 'wp-graphql-gravity-forms' ),
                ],
                'pageId' => [
                    'type'        => 'Integer',
                    'description' => __( 'Contains the Id of the WordPress page that the browser will be redirected to. Only applicable when type is set to page.', 'wp-graphql-gravity-forms' ),
                ],
                'queryString' => [
                    'type'        => 'String',
                    'description' => __( 'Contains the query string to be appended to the redirection url. Only applicable when type is set to redirect.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
