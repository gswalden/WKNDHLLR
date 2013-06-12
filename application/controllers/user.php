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
			$auth_response = $this->instagram_api->authorize($code);
			
			// Set up session variables containing some useful Instagram data
			$this->session->set_userdata('instagram-token', $auth_response->access_token);
			$this->session->set_userdata('instagram-username', $auth_response->user->username);
			$this->session->set_userdata('instagram-profile-picture', $auth_response->user->profile_picture);
			$this->session->set_userdata('instagram-user-id', $auth_response->user->id);
			$this->session->set_userdata('instagram-full-name', $auth_response->user->full_name);
			unset($auth_response->user->bio,$auth_response->user->website);
			$auth_response->user->access_token = $auth_response->access_token;
			
			$this->load->model('User_model');
			if ($this->User_model->add($auth_response->user))
				$this->session->set_flashdata('login', 'Log In Successful!');
			else
				$this->session->set_flashdata('login', 'Log In Denied!');

			redirect('/');
		}
		else
			redirect('/');

	}
}