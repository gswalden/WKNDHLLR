<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	public function index()
	{
		// $data['media'] = $this->instagram_api->getPopularMedia();
		// $this->load->view('home', $data);
	}

	public function code()
	{
		$get = $this->input->get('');
		
		if ( ! empty($get))
		{

		}
	}
}