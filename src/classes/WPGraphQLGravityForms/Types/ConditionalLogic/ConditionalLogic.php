<?php

namespace WPGraphQLGravityForms\Types\ConditionalLogic;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 *  Conditional Logic.
 *
 * @see https://docs.gravityforms.com/conditional-logic/
 */
class ConditionalLogic implements Hookable, Type {
    const TYPE = 'ConditionalLogic';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms conditional logic.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                // TODO: convert type to enum
                'actionType'   => [
                    'type'        => 'String',
                    'description' => __( 'The type of action the conditional logic will perform. Possible values: show, hide.', 'wp-graphql-gravity-forms' ),
                ],
                 // TODO: convert type to enum
                'logicType'   => [
                    'type'        => 'String',
                    'description' => __( 'Determines how to the rules should be evaluated. Possible values: any, all.', 'wp-graphql-gravity-forms' ),
                ],
                'rules'   => [
                    'type'        => [ 'list_of' => ConditionalLogicRule::TYPE ],
                    'description' => __( 'Conditional logic rules.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
