<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	public function index()
	{
		redirect('/');
	}

	public function code()
	{
		$get = $this->input->get('code');
		
		if ( ! empty($get))
		{
			$auth_response = $this->instagram_api->authorize($_GET['code']);

			// Set up session variables containing some useful Instagram data
			$this->session->set_userdata('instagram-token', $auth_response->access_token);
			$this->session->set_userdata('instagram-username', $auth_response->user->username);
			$this->session->set_userdata('instagram-profile-picture', $auth_response->user->profile_picture);
			$this->session->set_userdata('instagram-user-id', $auth_response->user->id);
			$this->session->set_userdata('instagram-full-name', $auth_response->user->full_name);

			redirect('/');
		}
		else
			redirect('/');

	}
}