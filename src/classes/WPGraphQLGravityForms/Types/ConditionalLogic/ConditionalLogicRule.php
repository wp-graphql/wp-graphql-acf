<?php

namespace WPGraphQLGravityForms\Types\ConditionalLogic;

use WPGraphQLGravityForms\Interfaces\Hookable;
use WPGraphQLGravityForms\Interfaces\Type;

/**
 *  Conditional Logic rule property.
 *
 * @see https://docs.gravityforms.com/conditional-logic/#rule-properties
 */
class ConditionalLogicRule implements Hookable, Type {
    const TYPE = 'ConditionalLogicRule';

    public function register_hooks() {
        add_action( 'graphql_register_types', [ $this, 'register_type' ] );
    }

    public function register_type() {
        register_graphql_object_type( self::TYPE, [
            'description' => __( 'Gravity Forms conditional logic rule.', 'wp-graphql-gravity-forms' ),
            'fields'      => [
                'fieldId'   => [
                    'type'        => 'Integer',
                    'description' => __( 'Target field Id. Field that will have it’s value compared with the value property to determine if this rule is a match.', 'wp-graphql-gravity-forms' ),
                ],
                // TODO: convert to enum.
                'operator'   => [
                    'type'        => 'String',
                    'description' => __( 'Operator to be used when evaluating this rule. Possible values: is, isnot, >, <, contains, starts_with, or ends_with.', 'wp-graphql-gravity-forms' ),
                ],
                'value'   => [
                    'type'        => 'String',
                    'description' => __( 'The value to compare with field specified by fieldId.', 'wp-graphql-gravity-forms' ),
                ],
            ],
        ] );
    }
}
