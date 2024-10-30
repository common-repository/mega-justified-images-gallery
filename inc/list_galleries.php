<?php

class MegaJig_Galleries_List_Table extends WP_List_Table {

	public function __construct() {
		// Set parent defaults.
		parent::__construct( array(
			'singular' => 'gallery',     // Singular name of the listed records.
			'plural'   => 'galleries',    // Plural name of the listed records.
			'ajax'     => false,       // Does this table support ajax?
		) );
	}

	public function get_columns() {
		$columns = array(
			'id'    => _x( 'ID', 'ID', 'megajig' ),
			'title'    => _x( 'Title', 'Title', 'megajig' ),
			'shortcode'   => _x( 'Shortcode', 'Shortcode', 'megajig' ),
			'images' => _x( 'Images', 'Images', 'megajig' ),
			'updated_at' => _x( 'Last Modify', 'Last Modify', 'megajig' ),
			'actions' => _x( 'Actions', 'Last Modify', 'megajig' ),
		);

		return $columns;
	}

	protected function get_sortable_columns() {
		$sortable_columns = array(
			'id'    => array( 'id', false ),
			'title'    => array( 'title', false ),
			'created_at'   => array( 'created_at', false ),
			'updated_at' => array( 'updated_at', false ),
		);

		return $sortable_columns;
	}

	function no_items() {
	  _e( 'No gallery found.', 'megajig' );
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'title':
				return $item[ $column_name ];

			case 'images':
				$items = json_decode($item['items']);
				return count($items);
			case 'updated_at':
				$orig_time = isset( $item['updated_at'] ) ? strtotime($item['updated_at']) : '';
	    		$time = date_i18n( 'Y/m/d @ g:i:s A', $orig_time );
	    		$human = human_time_diff( $orig_time );
	    		return sprintf( '<abbr title="%s">%s</abbr>', $time, $human . __(' ago', 'megajig') ) ;
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}
	}

	protected function column_shortcode( $item ) {
		return sprintf( '<input type="text" readonly value="[megajig id=\'%s\']" />', $item['id'] );
	}

	protected function column_actions( $item ) {
		$page = wp_unslash( $_REQUEST['page'] );
		// Build edit row action.
		$delete_query_args = array(
			'page'   => $page,
			'action' => 'delete',
			'id'  => $item['id'],
		);

		$duplicate_query_args = array(
			'page'   => $page,
			'action' => 'duplicate',
			'id'  => $item['id'],
		);

		$edit_query_args = array(
			'page'   => $page,
			'action' => 'edit',
			'id'  => $item['id'],
		);
		return sprintf(
			'<a class="megajig-list-action" href="%1$s" data-action="delete"><span class="dashicons dashicons-trash"></span> Delete</a>',
			esc_url( wp_nonce_url( add_query_arg( $delete_query_args, 'admin.php' ), 'deletegallery_' . $item['id'] ) )
		).
		sprintf(
			'<a class="megajig-list-action megajig-duplicate" href="%1$s" data-action="duplicate"><span class="dashicons dashicons-admin-page"></span> Duplicate</a>',
			esc_url( wp_nonce_url( add_query_arg( $duplicate_query_args, 'admin.php' ), 'duplicategallery_' . $item['id'] ) )
		)
		. sprintf(
			'<a class="megajig-list-action megajig-edit" href="%1$s"><span class="dashicons dashicons-edit"></span> Edit</a>',
			esc_url( wp_nonce_url( add_query_arg( $edit_query_args, 'admin.php' ), 'editgallery_' . $item['id'] ) )
		);
	}

	protected function column_title( $item ) {
		$page = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.

		// Build edit row action.
		$edit_query_args = array(
			'page'   => $page,
			'action' => 'edit',
			'id'  => $item['id'],
		);

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $edit_query_args, 'admin.php' ), 'editgallery_' . $item['id'] ) ),
			$item['title']
		);

	}

	protected function process_bulk_action() {
		// Detect when a bulk action is being triggered.
		if ( 'delete' === $this->current_action() ) {
			wp_die( 'Items deleted (or they would be if we had items to delete)!' );
		}
	}

	function prepare_items() {
		global $wpdb;

		$per_page = 5;

		$columns  = $this->get_columns();
		$hidden   = array();
		$data   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );


		//$this->process_bulk_action();
		$tableName = $wpdb->prefix . 'megajig';
		$galleries = $wpdb->get_results("SELECT *  FROM $tableName");
		foreach ($galleries as $gallery) {
			$data[] = (array) $gallery;
		}
		//$data = $this->example_data;


		$current_page = $this->get_pagenum();

		$total_items = count( $data );

		if($total_items > 0){
			usort( $data, array( $this, 'usort_reorder' ) );
			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		}


		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	protected function usort_reorder( $a, $b ) {
		$orderby = ! empty( $_REQUEST['orderby'] ) ? wp_unslash( $_REQUEST['orderby'] ) : 'title'; // WPCS: Input var ok.

		$order = ! empty( $_REQUEST['order'] ) ? wp_unslash( $_REQUEST['order'] ) : 'asc'; // WPCS: Input var ok.

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( 'asc' === $order ) ? $result : - $result;
	}
}
