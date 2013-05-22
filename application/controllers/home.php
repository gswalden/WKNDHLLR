<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index()
	{
		$data['media'] = $this->instagram_api->getPopularMedia();
		// var_dump($data['media']);
		$data['login_url'] = $this->instagram_api->instagramLogin();
		// Set the instagram library access token variable
 		$this->instagram_api->access_token = $this->session->userdata('instagram-token');
		$this->load->view('home', $data);
	}
}