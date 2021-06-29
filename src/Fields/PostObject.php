<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

class PostObject extends AcfField {

	/**
	 * @return string|string[]|void
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$type_name = $this->get_parent_type_fields_interface();

		$type_registry = $this->registry->get_type_registry();

		$connection_name = $this->registry->get_connection_name( $type_name, 'ContentNode', $this->field_name );

		$connection_config = [
			'fromType' => $type_name,
			'toType' => 'ContentNode',
			'fromFieldName' => $this->field_name,
			'resolve' => function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! is_array( $value ) ) {
					return null;
				}

				$value = array_map(function( $id ) {
					return absint( $id );
				}, $value );

				$resolver = new PostObjectConnectionResolver( $root, $args, $context, $info, 'any' );
				return $resolver
					->set_query_arg( 'post__in', $value )
					->set_query_arg( 'orderby', 'post__in' )
					->get_connection();
			}
		];

		if ( ! isset( $this->field_config['multiple'] ) || true !== (bool) $this->field_config['multiple'] ) {
			$connection_name = ucfirst( $type_name ) . 'ToSingleContentNodeConnection';
			$connection_config['connectionTypeName'] = $connection_name;
			$connection_config['oneToOne'] = true;
			$connection_config['resolve'] = function( $root, $args, AppContext $context, $info ) {
				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! absint( $value ) ) {
					return null;
				}

				$resolver = new PostObjectConnectionResolver( $root, $args, $context, $info, 'any' );
				return $resolver
					->one_to_one()
					->set_query_arg( 'p', absint( $value ) )
					->get_connection();
			};
		}

		// If the connection already exists, don't register it again
		if ( null !== $type_registry->get_type( $connection_name ) ) {
			return $type_registry->get_type( $connection_name );
		}

		$type_registry->register_connection( $connection_config );
		return null;
	}

}
