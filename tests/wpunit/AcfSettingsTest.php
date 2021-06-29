<?php

class AcfSettingsTest extends \Tests\WPGraphQL\TestCase\WPGraphQLTestCase {

	/**
	 * @var string
	 */
	public $group_key;

	public function setUp(): void {
		// before
		$this->group_key = __CLASS__;
		$this->clearSchema();
		$this->register_acf_field_group();
		parent::setUp();
		// your set up methods here
	}

	public function tearDown(): void {
		$this->deregister_acf_field_group();
		// your tear down methods here
		$this->clearSchema();
		// then
		parent::tearDown();
	}

	public function deregister_acf_field_group() {
		acf_remove_local_field_group( $this->group_key );
	}

	public function register_acf_field_group( $config = [] ) {

		$defaults = [
			'key'                   => $this->group_key,
			'title'                 => 'Post Object Fields',
			'fields'                => [],
			'location'              => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_graphql'       => 1,
			'graphql_field_name'    => 'postFieldsSettingsTest',
			'graphql_types'         => [ 'Post' ]
		];

		acf_add_local_field_group( array_merge( $defaults, $config ) );

	}

	public function register_acf_field( $config = [] ) {

		$defaults = [
			'parent'            => $this->group_key,
			'key'               => 'field_5d7812ert5tg',
			'label'             => 'Text',
			'name'              => 'text',
			'type'              => 'text',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'show_in_graphql'   => 1,
			'default_value'     => '',
			'placeholder'       => '',
			'prepend'           => '',
			'append'            => '',
			'maxlength'         => '',
		];

		acf_add_local_field( array_merge( $defaults, $config ) );
	}

	public function testBuildFragmentFromIntrospection() {

		$this->register_acf_field([
			'name'              => 'textFieldTest',
			'type'              => 'text',
		]);

		$this->clearSchema();

		$type_name = 'PostFieldsSettingsTest';
		$settings = new \WPGraphQL\ACF\AcfSettings();
		$fragment = $settings->get_graphql_type_from_introspection( $type_name );

		codecept_debug( $fragment );

		$this->assertSame( $type_name, $fragment['name'] );

	}

}
