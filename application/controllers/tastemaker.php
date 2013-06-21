<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tastemaker extends CI_Controller {

	protected $tags;

	public function __construct()
	{
        parent::__construct();
        
        // Check if user is an admin
		//if ($this->session->userdata('access_level') < ADMIN)
		//	redirect('/');

		$this->tags = [	
					['tag' => 'rave', 'max_id' => NULL],
					['tag' => 'vip', 'max_id' => NULL],
					['tag' => 'partybus', 'max_id' => NULL],
					['tag' => 'trap', 'max_id' => NULL]
					];

		$this->instagram_api->access_token = '387621951.14ddff3.4690ea24f5444c0fab9b0722b2c569c3';
	}

	public function index()
	{
		// Check response
		// $data->meta->code;

		// Set the instagram library access token variable
 		// $this->instagram_api->access_token = $this->session->userdata('instagram-token');

		$this->load->model('Photo_model');
		$data['photo_array'] = $this->Photo_model->get_photos_by_tags($this->tags);
		$data['pagination'] = $data['photo_array']['pagination'];
		$data['photo_array'] = $data['photo_array']['photos'];
		
		$this->load->view('basic', $data);
	}
}