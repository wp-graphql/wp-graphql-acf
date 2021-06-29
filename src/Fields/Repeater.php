<?php
namespace WPGraphQL\ACF\Fields;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Utils\Utils;

/**
 * Class Repeater
 *
 * @package WPGraphQL\ACF\Fields
 */
class Repeater extends AcfField {

	/**
	 * Returns the GraphQL Type to use in the Schema
	 *
	 * @return mixed|string|string[]|null
	 * @throws Exception
	 */
	public function get_graphql_type() {
		$parent_type = $this->get_parent_type();
		$title = $this->field_config['title'] ?? ( $this->field_config['label'] ?? 'no label or title' );
		$this->field_config['graphql_field_name'] = $parent_type . Utils::format_type_name( $title );
		$type_name = $this->registry->add_acf_field_group_to_graphql( $this->field_config, [ $parent_type ] );
		return [ 'non_null' => [ 'list_of' => $type_name ] ];
	}

	/**
	 * @param mixed       $node The parent node the repeater field belongs to
	 * @param array       $args The args passed to the field
	 * @param AppContext  $context The AppContext passed down the resolve tree
	 * @param ResolveInfo $info The ResolveInfo for the field
	 *
	 * @return array
	 */
	public function resolve( $node, array $args, AppContext $context, ResolveInfo $info ) {
		$value = parent::resolve( $node, $args, $context, $info );

		return ! empty( $value ) && is_array( $value ) ? $value : [];
	}

}
