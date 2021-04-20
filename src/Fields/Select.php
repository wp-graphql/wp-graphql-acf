<?php
namespace WPGraphQL\ACF\Fields;

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
	 * Return the value different based on single or mulitple selection allowed
	 *
	 * @param $node
	 * @param $args
	 * @param $context
	 * @param $info
	 *
	 * @return array|mixed|null
	 */
	public function resolve( $node, $args, $context, $info ) {
		$value = parent::resolve( $node, $args, $context, $info );
		if ( isset( $this->field_config['multiple'] ) && true === (bool) $this->field_config['multiple'] ) {
			return ! empty( $value ) && is_array( $value ) ? $value : [];
		}
		return $value;
	}

}
