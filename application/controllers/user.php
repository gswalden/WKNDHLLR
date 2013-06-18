<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

class User extends REST_Controller {

	public function __construct()
	{
		// remove in production
		$this->instagram_api->access_token = '387621951.14ddff3.4690ea24f5444c0fab9b0722b2c569c3';
		
		parent::__construct();
	}

	public function index()
	{
		redirect('/');
	}

	public function code()
	{
		$get = $this->get('code');
		
		if ( ! empty($get))
		{
			$auth_response = $this->instagram_api->authorize($code);
			
			// Set up session variables containing some useful Instagram data
			$this->session->set_userdata('instagram-token', $auth_response->access_token);
			$this->session->set_userdata('instagram-username', $auth_response->user->username);
			$this->session->set_userdata('instagram-profile-picture', $auth_response->user->profile_picture);
			$this->session->set_userdata('instagram-user-id', $auth_response->user->id);
			$this->session->set_userdata('instagram-full-name', $auth_response->user->full_name);
			$this->session->set_userdata('instagram-logged-in', TRUE);
			// Remove fields unsuitable for database insertion
			unset($auth_response->user->bio, $auth_response->user->website);
			$auth_response->user->access_token = $auth_response->access_token;
			
			$this->load->model('User_model');
			$add = $this->User_model->add($auth_response->user);
			if ($add['bool'])
				$this->session->set_flashdata('login', 'Log In Successful!');
			else
				$this->session->set_flashdata('login', 'Log In Denied!');

			redirect('/');
		}
		else
			redirect('/');
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('/');
	}

	public function user_get()
	{
		$id = $this->get('id');
		$this->load->model('User_model');
		$query = $this->User_model->get_user($id);
		if ($query->num_rows() > 0)
		{
			$this->_send_response([ 'code' => 200, 
									'data' => $query->row()	]); 
		}
		else
		{
			$this->_send_response(['code' => 404,
								'message' => 'User Not Found!'	]);
		}
	}

	public function users_get()
	{
		$get = $this->get();
		$this->load->model('User_model');
		$query = $this->User_model->get_users($get['access_level']);
		if ($query->num_rows() > 0)
		{
			$this->_send_response([ 'code' => 200, 
									'data' => $query->result()	]); 
		}
		else
		{
			$this->_send_response(['code' => 404,
								'message' => 'No Users Found!'	]);
		}
	}

	/**
	 * Send Response
	 *
	 * Sends RESTful response.
	 * 
	 * @param array $data Contains array of objects for JSON response and response code
	 */
	private function _send_response($data = NULL)
	{
		if ($data)
			$this->response($data, $data['code']);
		else
			$this->response(NULL);
	}
}