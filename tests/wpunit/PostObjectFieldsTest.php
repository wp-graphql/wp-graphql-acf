<?php

class PostObjectFieldsTest extends \Codeception\TestCase\WPTestCase {

	public $admin;
	public $post;
	public $test_cpt;
	public $page;

	/**
	 * This function is run before each method
	 * @since 0.0.5
	 */
	public function setUp() {

		parent::setUp();

		register_post_type( 'test', [
			'show_in_graphql' => true,
			'hierarchical' => true,
			'graphql_single_name' => 'Test',
			'graphql_plural_name' => 'Tests'
		] );

		$this->admin = $this->factory()->user->create( [
			'role' => 'administrator',
			'user_login' => 'testuser',
			'user_pass' => 'testPassword',
		] );

		$this->post = $this->factory()->post->create([
			'post_type' => 'post',
			'post_title' => 'ACF Test',
			'post_status' => 'publish',
			'post_author' => $this->admin
		]);

		$this->test_cpt = $this->factory()->post->create([
			'post_type' => 'test',
			'post_title' => 'ACF Test CPT',
			'post_status' => 'publish',
			'post_author' => $this->admin
		]);

		$this->page = $this->factory()->post->create([
			'post_type' => 'page',
			'post_title' => 'ACF Test Page',
			'post_status' => 'publish',
			'post_author' => $this->admin
		]);

		/**
		 * Register a field group with heaps of fields
		 */
		$this->register_fields();

	}

	/**
	 * Runs after each method.
	 * @since 0.0.5
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test querying a text field
	 */
	public function testQueryTextField() {

		$expected_text_1 = 'Some Text';
		update_field( 'text_field', $expected_text_1, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      textField
		    }
		  }  
		}';



		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected_text_1, $actual['data']['postBy']['postFields']['textField'] );

	}

	/**
	 * Test querying a text area field
	 */
	public function testQueryTextAreaField() {

		$expected_text_1 = 'Some Textarea Text';
		update_field( 'text_area_field', $expected_text_1, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      textAreaField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected_text_1, $actual['data']['postBy']['postFields']['textAreaField'] );

	}

	/**
	 * Test querying a number field
	 */
	public function testQueryNumberField() {

		$expected = absint(55 );
		update_field( 'number_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      numberField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( (float) $expected, $actual['data']['postBy']['postFields']['numberField'] );

	}

	/**
 * Test querying a range field
 */
	public function testQueryRangeField() {

		$expected = absint(66 );
		update_field( 'range_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      rangeField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['rangeField'] );

	}

	/**
	 * Test querying a email field
	 */
	public function testQueryEmailField() {

		$expected = 'test@test.com';
		update_field( 'email_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      emailField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['emailField'] );

	}

	/**
	 * Test querying a email field
	 */
	public function testQueryUrlField() {

		$expected = 'https://site.com';
		update_field( 'url_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      urlField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['urlField'] );

	}

	/**
	 * Test querying a password field
	 */
	public function testQueryPasswordField() {

		$expected = 'aserw3fgwv5467#$%$%^$';
		update_field( 'password_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      passwordField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['passwordField'] );

	}

	/**
	 * Test querying a image field
	 */
	public function testQueryImageField() {

		$filename      = ( WPGRAPHQL_PLUGIN_DIR . '/tests/_data/images/test.png' );
		$img_id = $this->factory()->attachment->create_upload_object( $filename );
		update_field( 'image_field', $img_id, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      imageField {
		        mediaItemId
		        thumbnail: sourceUrl(size: THUMBNAIL)
				medium: sourceUrl(size: MEDIUM)
				full: sourceUrl(size: LARGE)
				sourceUrl
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertEquals( [
			'mediaItemId' => $img_id,
			'thumbnail' => wp_get_attachment_image_src( $img_id, 'thumbnail' )[0],
			'medium' => wp_get_attachment_image_src( $img_id, 'medium' )[0],
			'full' => wp_get_attachment_image_src( $img_id, 'full' )[0],
			'sourceUrl' => wp_get_attachment_image_src( $img_id, 'full' )[0]
		], $actual['data']['postBy']['postFields']['imageField'] );

	}

	/**
	 * Test querying a File field
	 */
	public function testQueryFileField() {

		$filename      = ( WPGRAPHQL_PLUGIN_DIR . '/tests/_data/images/test.png' );
		$img_id = $this->factory()->attachment->create_upload_object( $filename );
		update_field( 'file_field', $img_id, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      fileField {
		        mediaItemId
				sourceUrl
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertEquals( [
			'mediaItemId' => $img_id,
			'sourceUrl' => wp_get_attachment_image_src( $img_id, 'full' )[0]
		], $actual['data']['postBy']['postFields']['fileField'] );

	}

	/**
	 * Test querying a Wysiwyg field
	 */
	public function testQueryWysiwygField() {

		$expected = 'some text';
		update_field( 'wysiwyg_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      wysiwygField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['wysiwygField'] );


	}

	/**
	 * Test querying a Wysiwyg field
	 */
	public function testQueryOembedField() {

		$expected = 'https://twitter.com/wpgraphql/status/1115652591705190400';
		update_field( 'oembed_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      oembedField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['oembedField'] );

	}

	/**
	 * Test querying a Wysiwyg field
	 */
	public function testQueryGalleryField() {

		/**
		 * Save Image IDs as the value for the gallery field
		 */
		$filename      = ( WPGRAPHQL_PLUGIN_DIR . '/tests/_data/images/test.png' );
		$img_id_1 = $this->factory()->attachment->create_upload_object( $filename );
		$img_id_2 = $this->factory()->attachment->create_upload_object( $filename );
		$img_ids = [ $img_id_1, $img_id_2 ];
		update_field( 'gallery_field', $img_ids, $this->post );

		/**
		 * Query for the gallery
		 */
		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      galleryField {
		        mediaItemId
		        sourceUrl
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( [
			[
				'mediaItemId' => $img_id_1,
				'sourceUrl' => wp_get_attachment_image_src( $img_id_1, 'full' )[0]
			],
			[
				'mediaItemId' => $img_id_2,
				'sourceUrl' => wp_get_attachment_image_src( $img_id_2, 'full' )[0]
			]
		], $actual['data']['postBy']['postFields']['galleryField'] );


	}

	/**
	 * Test querying a Select field
	 */
	public function testQuerySelectField() {

		$expected = 'one';
		update_field( 'select_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      selectField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['selectField'] );

	}

	/**
	 * Test querying a Checkbox field
	 */
	public function testQueryCheckboxField() {

		$expected = ['one'];
		update_field( 'checkbox_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      checkboxField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['checkboxField'] );

	}

	/**
	 * Test querying a Radio field
	 */
	public function testQueryRadioButtonField() {

		$expected = 'two';
		update_field( 'radio_button_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      radioButtonField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['radioButtonField'] );

	}

	/**
	 * Test querying a Button Group field
	 */
	public function testQueryButtonGroupField() {

		$expected = 'one';
		update_field( 'button_group_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      buttonGroupField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['buttonGroupField'] );

	}

	/**
	 * Test querying a True/False field
	 */
	public function testQueryTrueFalseField() {

		$expected = true;
		update_field( 'true_false_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      trueFalseField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['trueFalseField'] );

	}

	/**
	 * Test querying a Link field
	 */
	public function testQueryLinkField() {

		$expected = [
			'title' => 'Some Link',
			'url' => 'https://github.com/wp-graphql/wp-graphql',
			'target' => '_blank'
		];

		update_field( 'link_field', $expected, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      linkField {
		        title
		        url
		        target
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( $expected, $actual['data']['postBy']['postFields']['linkField'] );

	}

	/**
	 * Test querying a Post Object field
	 */
	public function testQueryPostObjectField() {

		$post_id = $this->post;

		update_field( 'post_object_field', $post_id, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      postObjectField {
		        __typename
		        ...on Post {
		          postId
		        }
		        ...on Page {
		          pageId
		        }
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( [
			'__typename' => 'Post',
			'postId' => $post_id,
		], $actual['data']['postBy']['postFields']['postObjectField'] );

	}

	/**
	 * Test querying a Post Object field
	 */
	public function testQueryPostObjectFieldWithPage() {

		$id = $this->page;

		update_field( 'post_object_field', $id, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      postObjectField {
		        __typename
		        ...on Post {
		          postId
		        }
		        ...on Page {
		          pageId
		        }
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( [
			'__typename' => 'Page',
			'pageId' => $id,
		], $actual['data']['postBy']['postFields']['postObjectField'] );

	}

	/**
	 * Test querying a Page Link field
	 */
	public function testQueryPageLinkField() {

		$id = $this->post;

		update_field( 'page_link_field', $id, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      pageLinkField {
		        __typename
		        ...on Post {
		          postId
		        }
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( [
			'__typename' => 'Post',
			'postId' => $id,
		], $actual['data']['postBy']['postFields']['pageLinkField'] );

	}

	/**
	 * Test querying a Page Link field
	 */
	public function testQueryPageLinkFieldWithError() {

		$id = $this->post;

		update_field( 'page_link_field', $id, $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      pageLinkField {
		        __typename
		        ...on Post {
		          postId
		        }
		        ...on Page {
		          pageId
		        }
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		/**
		 * Since the page_link_field is configured with just "post_type => [ 'post' ]",
		 * the union to return is just the "Post" type, so querying for
		 * a Page should throw an error here.
		 *
		 * Should see an error such as:
		 *
		 * Fragment cannot be spread here as objects of type "Post_PostFields_PageLinkField" can never be of type "Page".
		 */
		$this->assertArrayHasKey( 'errors', $actual );

	}

	public function testQueryFieldOnCustomPostType() {

		$id = $this->test_cpt;
		$expected_text_1 = 'test value';

		update_field( 'text_field', $expected_text_1, $id );

		$query = '
		query GET_CUSTOM_POST_TYPE_WITH_ACF_FIELD( $testId: Int! ) {
		  testBy( testId: $testId ) {
		    __typename
		    id
		    title
		    postFields {
		      textField
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'testId' => $id,
			],
		]);

		codecept_debug( $actual );

		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertEquals( $expected_text_1, $actual['data']['testBy']['postFields']['textField'] );


	}

	/**
	 * Test querying a Relationship field
	 */
	public function testQueryRelationshipField() {

		$post_id = $this->post;
		$page_id = $this->page;
		$filename      = ( WPGRAPHQL_PLUGIN_DIR . '/tests/_data/images/test.png' );
		$img_id = $this->factory()->attachment->create_upload_object( $filename );


		update_field( 'relationship_field', [ $post_id, $page_id, $img_id ], $this->post );

		$query = '
		query GET_POST_WITH_ACF_FIELD( $postId: Int! ) {
		  postBy( postId: $postId ) {
		    id
		    title
		    postFields {
		      relationshipField {
		        __typename
		        ...on Post {
		          postId
		        }
		        ...on Page {
		          pageId
		        }
		        ...on MediaItem {
		          mediaItemId
		        }
		      }
		    }
		  }  
		}';

		$actual = graphql([
			'query' => $query,
			'variables' => [
				'postId' => $this->post,
			],
		]);

		codecept_debug( $actual );

		$this->assertSame( [
			[
				'__typename' => 'Post',
				'postId' => $post_id,
			],
			[
				'__typename' => 'Page',
				'pageId' => $page_id,
			],
			[
				'__typename' => 'MediaItem',
				'mediaItemId' => $img_id,
			]
		], $actual['data']['postBy']['postFields']['relationshipField'] );

	}

	protected function register_fields() {

		add_action( 'init', function() {
			register_post_type( 'test', [
				'show_in_graphql' => true,
				'hierarchical' => true,
				'graphql_single_name' => 'Test',
				'graphql_plural_name' => 'Tests'
			] );
		});



		acf_add_local_field_group(array(
			'key' => 'group_5c8c7abfe98f7',
			'title' => 'Post Fields',
			'fields' => array(
				array(
					'key' => 'field_5c8c7ac6571de',
					'label' => 'Text Field',
					'name' => 'text_field',
					'type' => 'text',
					'instructions' => 'Instructions for the text field',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => 'textField',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array(
					'key' => 'field_5c8d7107e67a1',
					'label' => 'Text Area Field',
					'name' => 'text_area_field',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => '',
					'new_lines' => '',
				),
				array(
					'key' => 'field_5c8d7d304bb3a',
					'label' => 'Number Field',
					'name' => 'number_field',
					'type' => 'number',
					'instructions' => 'Add a number',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => 'customNumberFieldName',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => '',
					'max' => '',
					'step' => '',
				),
				array(
					'key' => 'field_5c8f2ab9f90aa',
					'label' => 'Range Field',
					'name' => 'range_field',
					'type' => 'range',
					'instructions' => 'Range field, dawg',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'default_value' => '',
					'min' => '',
					'max' => '',
					'step' => '',
					'prepend' => '',
					'append' => '',
				),
				array(
					'key' => 'field_5c8f2adb8e636',
					'label' => 'Email Field',
					'name' => 'email_field',
					'type' => 'email',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
				),
				array(
					'key' => 'field_5c8f2aea8e637',
					'label' => 'URL Field',
					'name' => 'url_field',
					'type' => 'url',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'default_value' => '',
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c8f2af38e638',
					'label' => 'Password Field',
					'name' => 'password_field',
					'type' => 'password',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
				),
				array(
					'key' => 'field_5c8f2aff8e639',
					'label' => 'Image Field',
					'name' => 'image_field',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'return_format' => 'array',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array(
					'key' => 'field_5c8f2b088e63a',
					'label' => 'File Field',
					'name' => 'file_field',
					'type' => 'file',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'return_format' => 'array',
					'library' => 'all',
					'min_size' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array(
					'key' => 'field_5c8f2b178e63b',
					'label' => 'WYSIWYG Field',
					'name' => 'wysiwyg_field',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'delay' => 0,
				),
				array(
					'key' => 'field_5c8f2b2d8e63c',
					'label' => 'oEmbed Field',
					'name' => 'oembed_field',
					'type' => 'oembed',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'width' => '',
					'height' => '',
				),
				array(
					'key' => 'field_5c8f2b378e63d',
					'label' => 'Gallery Field',
					'name' => 'gallery_field',
					'type' => 'gallery',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'min' => '',
					'max' => '',
					'insert' => 'append',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array(
					'key' => 'field_5c8f2b418e63e',
					'label' => 'Select Field',
					'name' => 'select_field',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'choices' => array(
						'one' => 'One',
						'two' => 'Two',
					),
					'default_value' => array(
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c8f2b518e63f',
					'label' => 'Checkbox Field',
					'name' => 'checkbox_field',
					'type' => 'checkbox',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'choices' => array(
						'one' => 'One',
						'two' => 'Two',
					),
					'allow_custom' => 0,
					'default_value' => array(
					),
					'layout' => 'vertical',
					'toggle' => 0,
					'return_format' => 'value',
					'save_custom' => 0,
				),
				array(
					'key' => 'field_5c8f2b5c8e640',
					'label' => 'Radio Button Field',
					'name' => 'radio_button_field',
					'type' => 'radio',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'choices' => array(
						'one' => 'One',
						'two' => 'Two',
					),
					'allow_null' => 0,
					'other_choice' => 0,
					'default_value' => '',
					'layout' => 'vertical',
					'return_format' => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key' => 'field_5c8f2b6c8e641',
					'label' => 'Button Group Field',
					'name' => 'button_group_field',
					'type' => 'button_group',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'choices' => array(
						'one' => 'One',
						'two' => 'Two',
					),
					'allow_null' => 0,
					'default_value' => '',
					'layout' => 'horizontal',
					'return_format' => 'value',
				),
				array(
					'key' => 'field_5c8f2b8d8e642',
					'label' => 'True False Field',
					'name' => 'true_false_field',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'message' => '',
					'default_value' => 0,
					'ui' => 0,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5c906944b8fea',
					'label' => 'Link Field',
					'name' => 'link_field',
					'type' => 'link',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'return_format' => 'array',
				),
				array(
					'key' => 'field_5c906a60cb595',
					'label' => 'Post Object Field',
					'name' => 'post_object_field',
					'type' => 'post_object',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'post_type' => [
						'post',
						'page'
					],
					'taxonomy' => '',
					'allow_null' => 0,
					'multiple' => 0,
					'return_format' => 'id',
					'ui' => 1,
				),
				array(
					'key' => 'field_5c906a7621090',
					'label' => 'Page Link Field',
					'name' => 'page_link_field',
					'type' => 'page_link',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'post_type' => [
						'post',
					],
					'taxonomy' => '',
					'allow_null' => 0,
					'allow_archives' => 0,
					'multiple' => 0,

				),
				array(
					'key' => 'field_5c906d3dc1e0f',
					'label' => 'Relationship Field',
					'name' => 'relationship_field',
					'type' => 'relationship',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'post_type' => [
						'post',
						'page',
						'attachment'
					],
					'taxonomy' => '',
					'filters' => array(
						0 => 'search',
						1 => 'post_type',
						2 => 'taxonomy',
					),
					'elements' => '',
					'min' => '',
					'max' => '',
					'return_format' => 'object',
				),
				array(
					'key' => 'field_5c906eb5d836e',
					'label' => 'Taxonomy Field',
					'name' => 'taxonomy_field',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'taxonomy' => 'category',
					'field_type' => 'checkbox',
					'add_term' => 1,
					'save_terms' => 0,
					'load_terms' => 0,
					'return_format' => 'id',
					'multiple' => 0,
					'allow_null' => 0,
				),
				array(
					'key' => 'field_5c907409a9b0f',
					'label' => 'User Field',
					'name' => 'user_field',
					'type' => 'user',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'role' => '',
					'allow_null' => 0,
					'multiple' => 0,
					'return_format' => 'array',
				),
				array(
					'key' => 'field_5c913a1bbaef7',
					'label' => 'Google Map Field',
					'name' => 'google_map_field',
					'type' => 'google_map',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'center_lat' => '',
					'center_lng' => '',
					'zoom' => '',
					'height' => '',
				),
				array(
					'key' => 'field_5c91490c792c2',
					'label' => 'Date Picker Field',
					'name' => 'date_picker_field',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'display_format' => 'd/m/Y',
					'return_format' => 'F j, Y',
					'first_day' => 0,
				),
				array(
					'key' => 'field_5c914924792c3',
					'label' => 'Date Time Picker Field',
					'name' => 'date_time_picker_field',
					'type' => 'date_time_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'display_format' => 'd/m/Y g:i a',
					'return_format' => 'd/m/Y g:i a',
					'first_day' => 0,
				),
				array(
					'key' => 'field_5c91493a792c4',
					'label' => 'Time Picker Field',
					'name' => 'time_picker_field',
					'type' => 'time_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'display_format' => 'g:i a',
					'return_format' => 'g:i a',
				),
				array(
					'key' => 'field_5c914945792c5',
					'label' => 'Color Picker Field',
					'name' => 'color_picker_field',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'default_value' => '',
				),
				array(
					'key' => 'field_5c914d2be1d78',
					'label' => 'Group Field',
					'name' => 'group_field',
					'type' => 'group',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'layout' => 'block',
					'sub_fields' => array(
						array(
							'key' => 'field_5c914da8bcf6f',
							'label' => 'Text Field In Group',
							'name' => 'text_field_in_group',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'show_in_graphql' => 1,
							'graphql_field_name' => '',
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_5c914e92bcf70',
							'label' => 'Text Area Field In Group',
							'name' => 'text_area_field_in_group',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'show_in_graphql' => 1,
							'graphql_field_name' => '',
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => '',
							'new_lines' => '',
						),
					),
				),
				array(
					'key' => 'field_5c91622b29f39',
					'label' => 'Repeater Field',
					'name' => 'repeater_field',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'graphql_field_name' => '',
					'collapsed' => '',
					'min' => 0,
					'max' => 0,
					'layout' => 'table',
					'button_label' => '',
					'sub_fields' => array(
						array(
							'key' => 'field_5c91623b29f3a',
							'label' => 'Text Field in Repeater',
							'name' => 'text_field_in_repeater',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'show_in_graphql' => 1,
							'graphql_field_name' => '',
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_5c916cc879e26',
							'label' => 'Image in Repeater',
							'name' => 'image_in_repeater',
							'type' => 'image',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'show_in_graphql' => 1,
							'graphql_field_name' => '',
							'return_format' => 'array',
							'preview_size' => 'thumbnail',
							'library' => 'all',
							'min_width' => '',
							'min_height' => '',
							'min_size' => '',
							'max_width' => '',
							'max_height' => '',
							'max_size' => '',
							'mime_types' => '',
						),
					),
				),
				array(
					'key' => 'field_5c916e316f20c',
					'label' => 'Flexible Field',
					'name' => 'flexible_field',
					'type' => 'flexible_content',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'layouts' => array(
						'layout_5c916e380a702' => array(
							'key' => 'layout_5c916e380a702',
							'name' => 'group_one',
							'label' => 'Group One',
							'display' => 'block',
							'sub_fields' => array(
								array(
									'key' => 'field_5c916e666f20d',
									'label' => 'Flex Group One',
									'name' => 'flex_group_one',
									'type' => 'group',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'show_in_graphql' => 1,
									'graphql_field_name' => '',
									'layout' => 'block',
									'sub_fields' => array(
										array(
											'key' => 'field_5c916e9a6f20e',
											'label' => 'Text',
											'name' => 'text',
											'type' => 'text',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'show_in_graphql' => 1,
											'graphql_field_name' => '',
											'default_value' => '',
											'placeholder' => '',
											'prepend' => '',
											'append' => '',
											'maxlength' => '',
										),
									),
								),
								array(
									'key' => 'field_5caebca9bad98',
									'label' => 'Repeater',
									'name' => 'repeater',
									'type' => 'repeater',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'show_in_graphql' => 1,
									'collapsed' => '',
									'min' => 0,
									'max' => 0,
									'layout' => 'table',
									'button_label' => '',
									'sub_fields' => array(
										array(
											'key' => 'field_5caebcc0bad99',
											'label' => 'Text',
											'name' => 'text',
											'type' => 'text',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array(
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'show_in_graphql' => 1,
											'default_value' => '',
											'placeholder' => '',
											'prepend' => '',
											'append' => '',
											'maxlength' => '',
										),
									),
								),
							),
							'min' => '',
							'max' => '',
						),
						'layout_5c916f24e716d' => array(
							'key' => 'layout_5c916f24e716d',
							'name' => 'group_2',
							'label' => 'Group Two',
							'display' => 'block',
							'sub_fields' => array(
								array(
									'key' => 'field_5c916f24e7170',
									'label' => 'Flex Image',
									'name' => 'flex_image',
									'type' => 'image',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array(
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'show_in_graphql' => 1,
									'graphql_field_name' => '',
									'return_format' => 'array',
									'preview_size' => 'thumbnail',
									'library' => 'all',
									'min_width' => '',
									'min_height' => '',
									'min_size' => '',
									'max_width' => '',
									'max_height' => '',
									'max_size' => '',
									'mime_types' => '',
								),
							),
							'min' => '',
							'max' => '',
						),
					),
					'button_label' => 'Add Row',
					'min' => '',
					'max' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					),
					array(
						'param' => 'post_status',
						'operator' => '==',
						'value' => 'publish',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'test',
					),
					array(
						'param' => 'post_status',
						'operator' => '==',
						'value' => 'publish',
					),
				),
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'category',
					),
				),
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'post_tag',
					),
				),
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'post_format',
					),
				),
				array(
					array(
						'param' => 'attachment',
						'operator' => '==',
						'value' => 'all',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
				),
				array(
					array(
						'param' => 'comment',
						'operator' => '==',
						'value' => 'post',
					),
				),
				array(
					array(
						'param' => 'comment',
						'operator' => '==',
						'value' => 'page',
					),
				),
				array(
					array(
						'param' => 'comment',
						'operator' => '==',
						'value' => 'attachment',
					),
				),
				array(
					array(
						'param' => 'nav_menu',
						'operator' => '==',
						'value' => 'all',
					),
				),
				array(
					array(
						'param' => 'nav_menu_item',
						'operator' => '==',
						'value' => 'all',
					),
				),
				array(
					array(
						'param' => 'options_page',
						'operator' => '==',
						'value' => '',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_graphql' => 1,
			'graphql_field_name' => 'postFields',
		));

	}

}
