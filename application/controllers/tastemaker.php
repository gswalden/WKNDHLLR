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
					'rave',
					'vip',
					'partybus',
					'trap'
					];
	}

	public function index()
	{
		// Check response
		// $data->meta->code;

		// Set the instagram library access token variable
 		$this->instagram_api->access_token = $this->session->userdata('instagram-token');
		
		$data['photo_array'] = $this->_pull_photos();
		
		// while (count($data['photo_array']) <= 50) 
		// {
		// 	$response = $this->_getPhotoArray($response->pagination->next_url);
		// 	$data['photo_array'] = array_merge($data['photo_array'], $response->data);			
		// }

		// var_dump($data['photo_array']);

		
		
		$this->load->view('basic', $data);
	}

	public function add($id)
	{
		$this->load->model('Photo_model');
		$add = $this->Photo_model->add_photo($id);
		if ($add['bool'])
			$this->session->set_flashdata('add_photo', 'Photo Added!');
		else
			$this->session->set_flashdata('add_photo', 'Photo Not Added!' . $add['message']);
		
		redirect('/tastemaker');
	}

	public function get_photos_by_tags($tag_list)
	{
		$this->tags = $tag_list;
		$this->_pull_photos();
	}

	protected function _pull_photos()
	{
		$photos = [];
		foreach ($this->tags as $tag) 
		{
			$response = $this->instagram_api->tagsRecent($tag);
			$photos = array_merge($photos, $response->data);
		}
		unset($tag);

		// remove duplicates
		$temp_array = [];
		foreach ($photos as &$photo) 
		{
			$temp_array[$photo->id] = $photo;
			unset($photo);
		}
		$photos = array_values($temp_array);
		unset($temp_array);		

		// sort array by created_time, newest first
		usort($photos, function($a, $b) {
		    return $b->created_time - $a->created_time;
		});
		
		return $photos;
	}

	protected function _getPhotoArray($url)
	{
		return $this->instagram_api->__apiCall($url);
	}
}