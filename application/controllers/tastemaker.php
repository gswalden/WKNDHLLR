<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tastemaker extends CI_Controller {

	public function index()
	{
		// Check if user is an admin
		//if ($this->session->userdata('access_level') < 2)
		//	redirect('/');

		// Check response
		// $data->meta->code;

		$data['login_url'] = $this->instagram_api->instagramLogin();
		// Set the instagram library access token variable
 		$this->instagram_api->access_token = $this->session->userdata('instagram-token');
		
		$response = $this->instagram_api->tagsRecent('baauer');
		$data['photo_array'] = $response->data;

		while (count($data['photo_array']) <= 50) 
		{
			$response = $this->getPhotoArray($response->pagination->next_url);
			$data['photo_array'] = array_merge($data['photo_array'], $response->data);			
		}

		// var_dump($data['photo_array']);

		$this->load->view('basic', $data);
	}

	protected function getPhotoArray($url)
	{
		return $this->instagram_api->__apiCall($url);
	}
}