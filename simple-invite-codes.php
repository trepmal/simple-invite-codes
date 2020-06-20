<?php
/*
Plugin Name: Simple Registration/Invite Codes
Description: Create invite codes to limit registration. Coded quickly, very simple, limited real-world testing
Author: Kailey Lampert
Author URI: http://kaileylampert.com/
*/

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
