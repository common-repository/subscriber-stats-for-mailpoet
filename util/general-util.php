<?php
/**
 * This file holds general utility functions that are used generally throughout the plugin.
 *
 * @package Mailpoet_Subscriber_Overview
 */

/**
 * Takes in an action string and returns a specific nonce.
 *
 * @param string $action Action for the nonce key.
 *
 * @return string $nonce
 */
function sjmailsub_nonce( $action = 'general' ) {
	$key = sjmailsub_nonce_key( $action );

	return wp_create_nonce( $key );
}

/**
 * Given an action string it outputs a secure nonce key.
 *
 * @param string $action Action for the nonce key.
 *
 * @return string
 */
function sjmailsub_nonce_key( $action = 'general' ) {
	// Filter input.
	$ac = filter_var( $action, FILTER_SANITIZE_STRING );

	return "geoconnect-$ac";
}



