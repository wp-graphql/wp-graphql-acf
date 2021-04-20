<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

class File extends AcfField {

	/**
	 * Registers a GraphQL connection instead of returning a scalar Type
	 *
	 * @return null
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$type_name = $this->get_parent_type();

		$type_registry = $this->registry->get_type_registry();

		$connection_name = ucfirst( $type_name ) . 'ToSingleMediaItemConnection';

		$type_registry->register_connection([
			'fromType' => $type_name,
			'toType' => 'MediaItem',
			'fromFieldName' => $this->field_name,
			'connectionTypeName' => $connection_name,
			'oneToOne' => true,
			'resolve' => function( $root, $args, AppContext $context, $info ) {

				$value = $this->resolve( $root, $args, $context, $info );

				if ( empty( $value ) || ! absint( $value ) ) {
					return null;
				}

				$resolver = new PostObjectConnectionResolver( $root, $args, $context, $info, 'attachment' );
				return $resolver
					->one_to_one()
					->set_query_arg( 'p', absint( $value ) )
					->get_connection();
			}
		]);

		return null;

	}

}
