<?php

namespace WPGraphQL\ACF\Fields;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class Select
 *
 * @package WPGraphQL\ACF\Fields
 */
class Select extends AcfField {

	/**
	 * Determine the Type based on whether the field is set to allow
	 * multiple selections or not
	 *
	 * @return string|string[]
	 */
	public function get_graphql_type() {
		if ( isset( $this->field_config['multiple'] ) && true === (bool) $this->field_config['multiple'] ) {
			return [ 'list_of' => 'String' ];
		}

		return 'String';
	}

	/**
	 * Return the value different based on single or multiple selection allowed
	 *
	 * @param mixed       $node    The node the field belongs to
	 * @param array       $args    The field arguments
	 * @param AppContext  $context The AppContext passed down the resolve tree
	 * @param ResolveInfo $info    The ResolveInfo passed down the resolve tree
	 *
	 * @return array|mixed|null
	 */
	public function resolve( $node, array $args, AppContext $context, ResolveInfo $info ) {
		$value = parent::resolve( $node, $args, $context, $info );
		if ( isset( $this->field_config['multiple'] ) && true === (bool) $this->field_config['multiple'] ) {
			return ! empty( $value ) && is_array( $value ) ? $value : null;
		}

		return $value;
	}

}
