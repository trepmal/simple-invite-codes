<?php
/*
Plugin Name: Simple Registration/Invite Codes
Description: Create invite codes to limit registration. Coded quickly, very simple, limited real-world testing
Author: Kailey Lampert
Author URI: http://kaileylampert.com/
*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require 'cli/class-simple-invite-codes.php';
	WP_CLI::add_command( 'sic', 'Simple_Invite_Codes' );
}

/**
 * Register post type
 */
function ic_cpt() {

	register_post_type( 'invite_codes', array(
		'label' => 'Codes',
		'public' => false,
		'show_ui' => true,
		'supports' => array( 'title', 'custom-fields' ),
	) );

}
add_action( 'init', 'ic_cpt' );

/**
 * Markup for registration form field
 */
function ic_add_register_field() {
	?>
	<p>
		<label for="invite_code"><?php echo esc_html( 'Invite Code' ); ?></label>
		<input type="text" name="invite_code" id="invite_code" class="input" size="25" />
	</p>
	<?php
}
add_action( 'register_form', 'ic_add_register_field' );

/**
 * Verify invite code
 *
 * @param string   $sanitized_user_login The submitted username after being sanitized.
 * @param string   $user_email           The submitted email.
 * @param WP_Error $errors               Contains any errors with submitted username and email,
 *                                       e.g., an empty field, an invalid username or email,
 *                                       or an existing username or email.
 * @return WP_Error|void
 */
function ic_add_register_field_validate( $sanitized_user_login, $user_email, $errors ) {

	// if there are other errors, do nothing with the invite code
	if ( ! empty( $errors->errors ) ) {
		return $errors;
	}

	// if code missing
	if ( ! isset( $_POST['invite_code'] ) || empty( $_POST['invite_code'] ) ) {
		return $errors->add( 'nocode', '<strong>Sorry</strong>, membership by invitation only.'  );
	}

	$given_code = sanitize_text_field( wpautop( $_POST['invite_code'] ) );

	// check if exists. Loose matching
	$is_valid_code = get_page_by_title( $given_code, OBJECT, 'invite_codes' );

	// if doesn't exist, or has been used already
	if ( is_null( $is_valid_code ) || 'draft' == $is_valid_code->post_status )  {

		return $errors->add( 'invalidcode', '<strong>ERROR</strong>: You provided an invalid code.' );

	// stricter matching of code (e.g. make it case-sensitive)
	} elseif ( $is_valid_code->post_title !== $given_code ) {

		return $errors->add( 'invalidcode', '<strong>ERROR</strong>: You provided an invalid code.' );

	// match
	} else {
		// if valid, mark as used by setting status to draft
		// this can be disabled to allow reuse
		wp_update_post( array(
			'ID' => $is_valid_code->ID,
			'post_status' => 'draft'
		) );
		// and record who used it in meta
		// only remembers the last registrant
		add_post_meta( $is_valid_code->ID, 'used_by', wp_json_encode( [ time(), $sanitized_user_login] ) );
	}
}
add_action( 'register_post', 'ic_add_register_field_validate', 10, 3 );

/**
 * Mark unique titles (codes)
 *
 * Due to verification via `get_page_by_title`, duplicate codes
 * are never usable.
 * Mark as duplicate and set to draft.
 *
 * @param array $data    An array of slashed post data.
 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
 * @return array An array of slashed post data.
 */
function ic_mark_duplicate_titles( $data, $postarr ) {
	if ( 'invite_codes' !== $data['post_type'] ) {
		return $data;
	}

	$title = $data['post_title'];
	$code_exists = get_page_by_title( $title, OBJECT, 'invite_codes' );
	if ( $code_exists ) {
		$data['post_title'] .= '-duplicate';
		$data['post_status'] = 'draft';
	}
	return $data;
}
add_filter( 'wp_insert_post_data', 'ic_mark_duplicate_titles', 10, 2 );