<?php
namespace elasticsearch;

class Hooks{
	function __construct(){
		add_action( 'transition_post_status', array( $this, 'determine_action_by_post_status' ), 10, 3 );
		add_action( 'delete_post', array( &$this, 'delete_post' ) );
	}

	function determine_action_by_post_status( $new_status, $old_status, $post ) {
		if( !is_object( $post ) || !in_array( $post->post_type, Config::types() ) ) {
			return;
		}

		$indexable_statuses = Config::indexable_statuses();
		$nonindexable_statuses = Config::nonindexable_statuses();

		if ( in_array( $new_status, $indexable_statuses ) ) {
			// includes updates to a post
			Indexer::addOrUpdate($post);
		}
		// If we had been indexable status before, and we're now a non-indexable status remove the post
		else if ( in_array( $new_status, $nonindexable_statuses ) && in_array( $old_status, $indexable_statuses ) ) {
			Indexer::delete($post);
		}
	}

	function delete_post( $post_id ) {
		if(is_object( $post_id )){
			$post = $post_id;
		} else {
			$post = get_post( $post_id );
		}

		if($post == null || !in_array($post->post_type, Config::types())){
			return;
		}

		Indexer::delete($post);
	}
}

new Hooks();
?>
