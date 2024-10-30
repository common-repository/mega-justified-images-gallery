<?php

class MegaJigGallery{

	public $id;

	public $items;

	public $title;

	public $source;

	public $options;

	public $created_at;

	public $updated_at;

	public $settings = '{}';

	public $attachments = array();

	protected $unset_fields;

	public function __construct() {
		$this->id = 0;
		$this->source = 'media';
		$this->title = 'New Gallery';
		$this->options = (object) array(
			'min_height' => 250,
			'gap' => 5,
			'gap_wrapper' => true,
			'last_row' => true,
			'lightbox' => array(
				'enable' => false,
			),
			'items_json' => array(),
			'title' => true,
			'description' => false,
		);

		$this->options = json_encode($this->options);

	}

	public function load($id) {
		global $wpdb;

		$tableName = $wpdb->prefix . 'megajig';
        $megjig_item = $wpdb->get_row( "SELECT * FROM $tableName WHERE `id` = $id" );

        if($megjig_item->id > 0){
        	foreach($megjig_item as $k => $v)
        		$this->set($k, $v);
        }
		$this->options = json_decode($this->options);
		$this->options->items_json = json_decode($this->items);
		$this->options = json_encode($this->options);

	}

	public function set($pro, $val) {
		$this->$pro = $val;

	}

	public function remove() {
		global $wpdb;

		$tableName = $wpdb->prefix . 'megajig';
		$where = array('id' => $this->id);
		$wpdb->delete( $tableName, $where);
	}

}
