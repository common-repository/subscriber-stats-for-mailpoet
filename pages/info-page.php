<?php
/**
 *
 * Functions for the info page for display.
 *
 * @package mailpoet_subscriber_overview
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
/**
 * Generates the markup for the addon subscriber page
 */
function sjmailsub_info_page() {
	$html = '';
	if ( isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'sj_view_stats' ) ) {
		$html .= '<div id="mailpoet-subpage">';

		$html .= '<h1>MailPoet Subscriber Stats</h1>';
		if ( isset( $_GET['sub'] ) ) {
			$sub   = intval( wp_unslash( $_GET['sub'] ) );
			$html .= '<p>Select a subscriber below to view their historical engagement with your MailPoet campaigns</p>';
			$html .= sjmailsub_get_all_subscriber_dropdown( $sub );

			// Testing the actual visualization.
			// Set up the donut string.
			$donut_string = '';

			// Get the subscriber data.
			$subscriber = sjmailsub_get_subscriber_data_by_id( $sub );

			if ( $subscriber ) {

				// Run all the queries.
				$received = sjmailsub_get_all_newsletters_by_sub( $sub );
				$opened   = sjmailsub_get_opened_newsletters_by_sub( $sub );
				$clicked  = sjmailsub_get_clicked_newsletters_by_sub( $sub );
				$unsubbed = sjmailsub_get_unsub_newsletters_by_sub( $sub );
				// Establish basic numbers.
				$total_count = count( $received );
				$open_count  = count( $opened );

				if ( $total_count > 0 ) {

					$opened_percent = ( $open_count / $total_count ) * 100;
					$click_count    = count( $clicked );
					$click_percent  = ( $click_count / $total_count ) * 100;
					$unsub_count    = count( $unsubbed );
					$unsub_percent  = ( $unsub_count / $total_count ) * 100;
					// Unopened is special.
					if ( $total_count > $open_count ) {
						$unopened = sjmailsub_get_unopened_newsletters( $received, $opened );
					} else {
						$unopened = null;
					}
					$unopened_count   = count( $unopened );
					$unopened_percent = ( $unopened_count / $total_count ) * 100;

					$html .= '<div class="m-all t-all d-all">';

					$html .= '<div class="m-all t-1of2 d-1of2">';
					$html .= '<h2>Subscriber Statistics for ' . $subscriber[0]['email'] . '</h2>';
					// Get the canvas for the chart donut.
					$html .= '<canvas id="subscriber-doughnut"></canvas>';
					$html .= '</div>';

					$html .= '<div class="m-all t-1of2 d-1of2">';

					// Basic top level analytics.
					$html .= '<h2>' . $open_count . ' of ' . $total_count . ' (' . $opened_percent . '%) Newsletters Opened.</h2>';

					$html .= '<h2>' . $click_count . ' of ' . $total_count . ' (' . $click_percent . '%) Newsletters Clicked.</h2>';
					$html .= '<p>These statistics are calculated for the last year</p>';
					// Add a link to where the user can edit the subscriber.
					$html .= '<a href="/wp-admin/admin.php?page=mailpoet-subscribers#/edit/' . $sub . '" target="_blank" class="btn button">Edit Subscriber Here</a>';

					// Get the canvas for the chart donut.
					$html .= '<canvas id="subscriber-doughnut"></canvas>';

					$html .= '</div>';
					$html .= '</div>';

					// Generate the links to filter by.
					$html .= sjmailsub_get_filter_links();

					// Donut string.
					$donut_string = sjmailsub_build_donut_string( $donut_string, $unopened_percent, 'Unopened' );
					$donut_string = sjmailsub_build_donut_string( $donut_string, $unsub_percent, 'Unsubscribed', '#194655' );
					$donut_string = sjmailsub_build_donut_string( $donut_string, $opened_percent, 'Opened', '#f05400' );
					$donut_string = sjmailsub_build_donut_string( $donut_string, $click_percent, 'Clicked', '#4abeac' );

					if ( isset( $_GET['type'] ) ) {
						$type = sanitize_text_field( wp_unslash( $_GET['type'] ) );
						// Show a different table based on the query var.
						if ( 'all' === $type ) {
							$html .= '<h4>Newsletters</h4>';
							// Build the table markup.
							$html .= sjmailsub_get_newsletter_table( $received );
						} elseif ( 'clicked' === $type ) {
							$html .= '<h4>Clicked Newsletters</h4>';
							// Build table markup.
							$html .= sjmailsub_get_newsletter_table( $clicked );
						} elseif ( 'opened' === $type ) {
							$html .= '<h4>Opened Newsletters</h4>';
							// Build the table markup.
							$html .= sjmailsub_get_newsletter_table( $opened );
						} elseif ( 'unsub' === $type ) {
							$html .= '<h4>Unsubscribed Newsletters</h4>';
							// Build table markup.
							$html .= sjmailsub_get_newsletter_table( $unsubbed );
						} elseif ( 'unopened' === $type ) {
							$html .= '<h4>Unopened Newsletters</h4>';
							// Build table markup.
							$html .= sjmailsub_get_newsletter_table( $unopened );
						}// End if get type.
					}

					$html .= '</div>';

					// Get the just received count.
					$html .= '<div id="donut-amounts">' . $donut_string . '</div>';
				} else {
					$html .= '<h4>Subscriber Has Not Been Sent Emails</h4>';
				}
			} else {
				$html .= '<h4>Subscriber Does Not Exist</h4>';
			}

			$html .= '</div>';
		} else {
			$html .= sjmailsub_get_all_subscriber_dropdown( '' );
			$html .= '</div>';
		}
	}
	echo $html;
}

/**
 * Gets the markup for the links
 *
 * @return string markup
 */
function sjmailsub_get_filter_links() {
	$html = '<div class="m-all t-all d-all">';
	if ( isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'sj_view_stats' ) ) {
		// Query vars for links.
		if ( isset( $_GET['sub'] ) && isset( $_GET['page'] ) && isset( $_GET['type'] ) ) {
			$sub  = intval( wp_unslash( $_GET['sub'] ) );
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			$type = sanitize_text_field( wp_unslash( $_GET['type'] ) );

			$html .= '<div class="m-all t-all d-all filters">';
			if ( 'all' === $type ) {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=all&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '" class="active">All</a> | ';
			} else {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=all&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '">All</a> | ';
			}
			if ( 'unopened' === $type ) {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=unopened&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '" class="active">Unopened</a> | ';
			} else {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=unopened&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '">Unopened</a> | ';
			}
			if ( 'opened' === $type ) {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=opened&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '" class="active">Opened</a> | ';
			} else {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=opened&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '">Opened</a> | ';
			}
			if ( 'clicked' === $type ) {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=clicked&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '" class="active">Clicked</a> | ';
			} else {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=clicked&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '">Clicked</a> | ';
			}
			if ( 'unsub' === $type ) {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=unsub&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '" class="active">Unsubscribed</a>';
			} else {
				$html .= '<a href="/wp-admin/admin.php?page=' . $page . '&sub=' . $sub . '&type=unsub&nonce=' . wp_create_nonce( 'sj_view_stats' ) . '">Unsubscribed</a>';
			}

			$html .= '</div>';
		}
	}

	return $html;
}

/**
 *
 * Determines the newsletters that are unopened
 *
 * @param array $unopened All unoponed newsletters.
 * @param array $opened All opened newsletters.
 *
 * @return array unopened newsletters
 */
function sjmailsub_get_unopened_newsletters( $unopened, $opened ) {
	$opened_newsletter_ids = array();
	if ( $opened ) {
		foreach ( $opened as $open ) {
			$opened_newsletter_ids[] = $open['newsletter_id'];
		}
	}
	if ( $unopened ) {
		foreach ( $unopened as $index => $unopen ) {
			if ( in_array( $unopen['newsletter_id'], $opened_newsletter_ids, true ) ) {
				unset( $unopened[ $index ] );
			}
		}
	}

	return $unopened;
}

/**
 *
 * Builds the donut string for the newsletters
 *
 * @param string $donut_string Existing donut string.
 * @param int    $percent Percent of total.
 * @param string $label Label for the graph.
 * @param string $color Colour for the graph.
 *
 * @return string donutstring
 */
function sjmailsub_build_donut_string( $donut_string, $percent, $label, $color = '#e6e6e6' ) {
	$donut_string .= '<div data-amount="' . $percent . '" data-label="' . $label . ' (' . $percent . '%)" data-color="' . $color . '"></div>';

	return $donut_string;
}

/**
 *
 * Retrieves the newsletter db row by id.
 *
 * @param int $news_id ID of the newsletter to fetch.
 *
 * @return array $results
 */
function sjmailsub_get_newsletter_by_id( $news_id ) {
	global $wpdb;
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_newsletters WHERE id = %d", $news_id ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all newsletters sent to a subscriber by subscriber id.
 *
 * @param int $sub_id ID of the subscriber to fetch all newsletters for.
 *
 * @return array $results
 */
function sjmailsub_get_all_newsletters_by_sub( $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$new_time = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_newsletters WHERE subscriber_id = %d AND sent_at > %s ORDER BY sent_at DESC", $sub_id, $new_time ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all newsletters opened by a subscriber.
 *
 * @param int $sub_id ID of the subscriber to fetch all opened newsletters for.
 *
 * @return array $results
 */
function sjmailsub_get_opened_newsletters_by_sub( $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$new_time = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_opens WHERE subscriber_id = %d AND created_at > %s ORDER BY created_at DESC", $sub_id, $new_time ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all particular newsletters clicked by a subscriber.
 *
 * @param int $news_id ID of the clicked newsletters.
 * @param int $sub_id ID of the subscriber to get newsletters for.
 *
 * @return array $results
 */
function sjmailsub_get_opened_newsletters_by_news_and_sub( $news_id, $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$new_time = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_opens WHERE subscriber_id = %d AND newsletter_id = %d AND created_at > %s ORDER BY created_at DESC", $sub_id, $news_id, $new_time ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all newsletters clicked by a subscriber.
 *
 * @param int $sub_id ID of the subscriber to fetch clicked newsletters for.
 *
 * @return array $results
 */
function sjmailsub_get_clicked_newsletters_by_sub( $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$newtime = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_clicks WHERE subscriber_id = %d AND created_at > %s ORDER BY created_at DESC", $sub_id, $newtime ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all particular newsletters clicked by a subscriber.
 *
 * @param int $news_id ID of the newsletter clicked.
 * @param int $sub_id ID of the subscriber.
 *
 * @return array $results
 */
function sjmailsub_get_clicked_newsletters_by_news_and_sub( $news_id, $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$new_time = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_clicks WHERE subscriber_id = %d AND newsletter_id = %d AND created_at > %s ORDER BY created_at DESC", $sub_id, $news_id, $new_time ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all newsletters that caused a subscribe to unsubscribe.
 *
 * @param int $sub_id The ID of the user to fetch the unsubcribed newsletters for.
 *
 * @return array $results
 */
function sjmailsub_get_unsub_newsletters_by_sub( $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$new_time = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_unsubscribes WHERE subscriber_id = %d AND created_at > %s ORDER BY created_at DESC", $sub_id, $new_time ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves all newsletters that caused a subscriber to unsubscribe.
 *
 * @param int $news_id The ID of the newsletter to retrieve.
 * @param int $sub_id The ID of the subscriber to retrieve all newsletters for.
 *
 * @return array $results
 */
function sjmailsub_get_unsubbed_newsletters_by_news_and_sub( $news_id, $sub_id ) {
	global $wpdb;
	$results = array();
	try {
		$time = new DateTime( 'now' );
	} catch ( Exception $e ) {
		return $results;
	}
	$newtime = $time->modify( '-1 year' )->format( 'Y-m-d H:i:s' );
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_statistics_unsubscribes WHERE subscriber_id = %d AND newsletter_id = %d AND created_at > %s ORDER BY created_at DESC", $sub_id, $news_id, $newtime ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}


/**
 *
 * Retrieves subscriber data by subscriber id.
 *
 * @param int $sub_id The ID of the subscriber to retrive all the data for.
 *
 * @return array $results
 */
function sjmailsub_get_subscriber_data_by_id( $sub_id ) {
	global $wpdb;
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_subscribers WHERE id = %d", $sub_id ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Retrieves link data by link id.
 *
 * @param int $link_id The ID of the link to retrieve.
 *
 * @return array $results
 */
function sjmailsub_get_link_by_id( $link_id ) {
	global $wpdb;
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailpoet_newsletter_links WHERE id = %d", $link_id ), ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Builds markup for a single newsletter table row.
 *
 * @param array $newsletter Table row for newsletter.
 * @param int   $subscriber_id Id of the subscriber.
 *
 * @return string $html
 */
function sjmailsub_get_display_newsletter_row( $newsletter, $subscriber_id ) {

	$subject = $newsletter[0]['subject'];
	$sent_at = $newsletter[0]['sent_at'];

	$newsletter_link = '<a href="/wp-admin/admin.php?page=mailpoet-newsletter-editor&id=' . $newsletter[0]['id'] . '" target="_blank">' . $subject . '</a>';

	$clicks = sjmailsub_get_clicked_newsletters_by_news_and_sub( $newsletter[0]['id'], $subscriber_id );
	$opens  = sjmailsub_get_opened_newsletters_by_news_and_sub( $newsletter[0]['id'], $subscriber_id );
	$unsubs = sjmailsub_get_unsubbed_newsletters_by_news_and_sub( $newsletter[0]['id'], $subscriber_id );

	// Get the click analytics ready.
	$click_total = 0;
	$link_string = '';

	if ( $clicks ) {
		foreach ( $clicks as $click ) {
			$click_total  = $click_total + $click['count'];
			$link         = sjmailsub_get_link_by_id( $click['link_id'] );
			$link_string .= $link[0]['url'] . '<br/>';
		}
	} else {
		$link_string = 'No links clicked';
	}
	$open_date = '';
	// Get the open analytics ready.
	if ( $opens ) {
		foreach ( $opens as $open ) {
			$open_date = $open['created_at'];
		}
	} else {
		$open_date = 'Unopened';
	}
	$unsub_date = '';
	// Get the open analytics ready.
	if ( $unsubs ) {
		foreach ( $unsubs as $unsub ) {
			$unsub_date = $unsub['created_at'];
		}
	} else {
		$unsub_date = 'Not unsubscribed';
	}

	$html  = '<tr>';
	$html .= '<td>' . $newsletter_link . '</td>';
	$html .= '<td>' . $open_date . '</td>';
	$html .= '<td>' . $link_string . '</td>';
	$html .= '<td>' . $click_total . '</td>';
	$html .= '<td>' . $unsub_date . '</td>';
	$html .= '<td>' . $sent_at . '</td>';
	$html .= '</tr>';

	return $html;
}

/**
 *
 * Builds markup for the subscriber dropdown.
 *
 * @param int $sub The ID of the subscriber to build markup for.
 * @return string $html
 */
function sjmailsub_get_all_subscriber_dropdown( $sub ) {
	$subscribers = sjmailsub_get_all_subscriber();
	$html        = '';
	if ( $subscribers ) {
		$html .= '<form action="" method="GET">';
		$html .= '<input type="hidden" name="page" value="wjmailsub-stats" />';
		$html .= '<input type="hidden" name="type" value="all" />';
		$html .= '<input type="hidden" name="nonce" value="' . wp_create_nonce( 'sj_view_stats' ) . '" />';
		$html .= '<select name="sub" class="sequel">';
		$html .= '<option name="sub" disabled">Select a Subscriber</option>';
		foreach ( $subscribers as $subscriber ) {
			if ( $sub === $subscriber['id'] ) {
				$html .= '<option name="sub" selected value="' . $subscriber['id'] . '">' . $subscriber['email'] . '</option>';
			} else {
				$html .= '<option name="sub" value="' . $subscriber['id'] . '">' . $subscriber['email'] . '</option>';
			}
		}
		$html .= '</select>';
		$html .= '<input type="submit" value="Find Subscriber">';
		$html .= '</form>';
	}

	return $html;
}

/**
 *
 * Gets all subscribers for this site
 *
 * @return array $results
 */
function sjmailsub_get_all_subscriber() {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mailpoet_subscribers", ARRAY_A ); // db call ok; no-cache ok.

	return $results;
}

/**
 *
 * Build markup for a regular newsletter table
 *
 * @param array $array Array containing data to build markup.
 *
 * @return string $html
 */
function sjmailsub_get_newsletter_table( $array ) {

	$html = '<table class="wp-list-table widefat fixed striped posts"><thead><th id="subject">Subject</th><th id="opens">Opened Date</th><th id="link">Link</th><th id="clicks">Clicks</th><th id="unsub">Unsubscribed Date</th><th id="sent">Date Sent</th></thead>';

	if ( $array ) {
		foreach ( $array as $newsletter_array ) {

			$newsletter    = sjmailsub_get_newsletter_by_id( $newsletter_array['newsletter_id'] );
			$subscriber_id = $newsletter_array['subscriber_id'];

			$html .= sjmailsub_get_display_newsletter_row( $newsletter, $subscriber_id );
		}
	} else {
		$html .= '<tr><td colspan="6">No Data</td></tr>';
	}

	$html .= '</table>';

	return $html;
}
