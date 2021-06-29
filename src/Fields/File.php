<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

/**
 * Class File
 *
 * @package WPGraphQL\ACF\Fields
 */
class File extends AcfField {

	/**
	 * Registers a GraphQL connection instead of returning a scalar Type
	 *
	 * @return string
	 * @throws Exception
	 */
	public function get_graphql_type() {

		$type_name = $this->get_parent_type_fields_interface();

		$type_registry = $this->registry->get_type_registry();

		$connection_name = ucfirst( $type_name ) . 'ToSingleMediaItemConnection';

		// If the connection already exists, don't register it again
		if ( $type_registry->get_type( $connection_name ) ) {
			return $connection_name;
		}

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
