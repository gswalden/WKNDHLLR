<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tastemaker extends CI_Controller {

	public function index()
	{
		// Check if user is an admin
		if ($this->session->userdata('access_level') < 2)
			redirect('/');

		$data
		$data['media'] = $this->instagram_api->getPopularMedia();
		// var_dump($data['media']);
		$data['login_url'] = $this->instagram_api->instagramLogin();
		// Set the instagram library access token variable
 		$this->instagram_api->access_token = $this->session->userdata('instagram-token');
		$this->load->view('tastemaker', $data);
	}
}