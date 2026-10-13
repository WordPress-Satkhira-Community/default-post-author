<?php
/**
 * WP-CLI commands for Default Post Author.
 *
 * @package DefaultPostAuthor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage Default Post Author via WP-CLI.
 */
class DPAP_CLI_Command {

	/**
	 * Set the default author.
	 *
	 * ## OPTIONS
	 *
	 * --author=<id>
	 * : User ID of the default author.
	 *
	 * [--post-types=<types>]
	 * : Comma-separated list of post types (default: post).
	 *
	 * ## EXAMPLES
	 *
	 *     wp dpap set-author --author=5
	 *     wp dpap set-author --author=5 --post-types=post,page
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function set_author( $args, $assoc_args ) {
		$author_id = isset( $assoc_args['author'] ) ? absint( $assoc_args['author'] ) : 0;

		if ( ! $author_id || ! get_userdata( $author_id ) ) {
			WP_CLI::error( 'Invalid author ID.' );
		}

		$post_types = array( 'post' );
		if ( ! empty( $assoc_args['post-types'] ) ) {
			$post_types = array_map( 'sanitize_key', explode( ',', $assoc_args['post-types'] ) );
		}

		$options = get_option( DPAP_OPTIONS, array() );
		$options['default_author']     = $author_id;
		$options['enabled_post_types'] = $post_types;
		$options['enable_default_author'] = true;

		update_option( DPAP_OPTIONS, $options );

		WP_CLI::success( sprintf( 'Default author set to user ID %d for post types: %s', $author_id, implode( ', ', $post_types ) ) );
	}

	/**
	 * Bulk update existing posts to the default author.
	 *
	 * ## OPTIONS
	 *
	 * [--author=<id>]
	 * : Override the stored default author for this run.
	 *
	 * [--post-types=<types>]
	 * : Comma-separated list of post types (default: from settings).
	 *
	 * [--status=<status>]
	 * : Post status filter (publish, draft, pending, private, any). Default: any.
	 *
	 * [--dry-run]
	 * : Show what would be updated without making changes.
	 *
	 * ## EXAMPLES
	 *
	 *     wp dpap bulk-update
	 *     wp dpap bulk-update --dry-run
	 *     wp dpap bulk-update --author=5 --post-types=post,page --status=publish
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function bulk_update( $args, $assoc_args ) {
		$default_author = isset( $assoc_args['author'] ) ? absint( $assoc_args['author'] ) : (int) dpap_get_option( 'default_author', 1 );

		if ( ! get_userdata( $default_author ) ) {
			WP_CLI::error( 'Invalid author ID.' );
		}

		$post_types = dpap_get_enabled_post_types();
		if ( ! empty( $assoc_args['post-types'] ) ) {
			$post_types = array_map( 'sanitize_key', explode( ',', $assoc_args['post-types'] ) );
		}

		$status = isset( $assoc_args['status'] ) ? sanitize_key( $assoc_args['status'] ) : 'any';
		$dry_run = isset( $assoc_args['dry-run'] );

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => $status,
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'paged'          => 1,
		);

		$total_updated = 0;
		$total_skipped = 0;

		WP_CLI::log( $dry_run ? 'Running in dry-run mode…' : 'Starting bulk update…' );

		while ( true ) {
			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				break;
			}

			foreach ( $query->posts as $post_id ) {
				if ( 'auto-draft' === get_post_status( $post_id ) ) {
					++$total_skipped;
					continue;
				}

				$current = (int) get_post_field( 'post_author', $post_id );
				if ( $current === $default_author ) {
					++$total_skipped;
					continue;
				}

				if ( ! $dry_run ) {
					$result = wp_update_post(
						array(
							'ID'          => $post_id,
							'post_author' => $default_author,
						),
						true
					);

					if ( ! is_wp_error( $result ) ) {
						++$total_updated;
					} else {
						++$total_skipped;
					}
				} else {
					++$total_updated;
				}
			}

			++$args['paged'];
			WP_CLI::log( sprintf( 'Processed page %d…', $args['paged'] - 1 ) );
		}

		if ( $dry_run ) {
			WP_CLI::success( sprintf( 'Dry-run complete. %d posts would be updated, %d skipped.', $total_updated, $total_skipped ) );
		} else {
			WP_CLI::success( sprintf( 'Done. %d posts updated, %d skipped.', $total_updated, $total_skipped ) );
		}
	}
}

WP_CLI::add_command( 'dpap', 'DPAP_CLI_Command' );
