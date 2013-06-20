<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

class Admin extends REST_Controller {

	public function __construct()
	{
		// remove in production
		$this->instagram_api->access_token = '387621951.14ddff3.4690ea24f5444c0fab9b0722b2c569c3';
		
		parent::__construct();
	}

	/**
	 * User
	 *
	 * Get user information.
	 * 
	 */
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
		$this->_send_response(['code' => 404,
							'message' => 'User Not Found!'	]);
	}
	/**
	 * Users
	 *
	 * Gets all users.
	 * 
	 */
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
		$this->_send_response(['code' => 404,
							'message' => 'No Users Found!'	]);
	}

	/**
	 * Delete User
	 *
	 * Deletes a user.
	 * 
	 */
	public function delete_user_post()
	{
		$id = $this->post('id');
		$this->load->model('User_model');
		if ($this->User_model->delete_user($id))
		{
			$this->_send_response(['code' => 200, 
								'message' => 'User Deleted!'	]); 
		}
		$this->_send_response(['code' => 404,
							'message' => 'User Not Found!'	]);
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