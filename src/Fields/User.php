<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\UserConnectionResolver;

/**
 * Class User
 *
 * @package WPGraphQL\ACF\Fields
 */
class User extends AcfField  {

	/**
	 * Determines the GraphQL Type for user fields
	 *
	 * @return string|string[]|void
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$type_name = $this->get_parent_type();
		$type_registry = $this->registry->get_type_registry();
		$connection_config = [
			'fromType' => $type_name,
			'toType' => 'User',
			'fromFieldName' => $this->field_name,
			'resolve' => function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! is_array( $value ) ) {
					return null;
				}

				$value = array_map(function( $id ) {
					return absint( $id );
				}, $value );

				$resolver = new UserConnectionResolver( $root, $args, $context, $info );
				return $resolver
					->set_query_arg( 'include', $value )
					->get_connection();
			}
		];

		if ( ! isset( $this->field_config['multiple'] ) || true !== (bool) $this->field_config['multiple'] ) {
			$connection_config['connectionTypeName'] = ucfirst( $type_name ) . 'ToSingleUserConnection';
			$connection_config['oneToOne'] = true;
			$connection_config['resolve'] = function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! absint( $value ) ) {
					return null;
				}

				$resolver = new UserConnectionResolver( $root, $args, $context, $info );
				return $resolver
					->one_to_one()
					->set_query_arg( 'include', absint( $value ) )
					->get_connection();
			};
		}

		$type_registry->register_connection( $connection_config );

	}

}
