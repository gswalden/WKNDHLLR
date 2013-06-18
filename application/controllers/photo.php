<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

class Photo extends REST_Controller {

	public function __construct()
	{
		// remove in production
		$this->instagram_api->access_token = '387621951.14ddff3.4690ea24f5444c0fab9b0722b2c569c3';
		
		parent::__construct();
	}

	/**
	 * Get Photo
	 *
	 * Returns database row for specified photo.
	 * 
	 * @param  string $id Instagram-assigned media_id
	 */
	public function get_photo_get($id)
	{
		$this->load->model('Photo_model');
		$query = $this->Photo_model->get_photo($id);
		if ($query->num_rows() > 0)
		{
			$this->_send_response([ 'code' => 200, 
									'data' => $query->row()	]); 
		}
		else
		{
			$this->_send_response(['code' => 404,
								'message' => 'Photo Not Found!'	]);
		}
	}

	/**
	 * Tags
	 *
	 * Returns photos with given tag(s) from Instagram, sorted by date and duplicates removed
	 * 
	 */
	public function tags_post()
	{
		$post = $this->post();
		if ($post)
		{
			foreach ($post as $key => $value) 
			{
				$tags[] = ['tag' => $key, 'max_id' => $value];
			}
			$this->load->model('Photo_model');
			$data['data'] = $this->Photo_model->get_photos_by_tags($tags);
			$data['pagination'] = $data['data']['pagination'];
			$data['data'] = $data['data']['photos'];
			if ($data['data'])
			{
				$data['code'] = 200;
				$this->_send_response($data);
			}
		}
		$this->_send_response(['code' => 404,
							'message' => 'Tags Not Found!'	]);
	}

	public function approve_suggestion_post()
	{
		$this->post('id');
		$this->load->model('Photo_model');
		if ($this->Photo_model->approve_suggested_photo($id))
			$this->_send_response(['code' => 200,
								'message' => 'Photo Approved!'	]);
		$this->_send_response(['code' => 404,
							'message' => 'Photo Not Found!'	]);
	}

	public function remove_suggestion_post()
	{
		$this->post('id');
		$this->load->model('Photo_model');
		if ($this->Photo_model->remove_suggested_photo($id))
			$this->_send_response(['code' => 200,
								'message' => 'Photo Removed!'	]);
		$this->_send_response(['code' => 404,
							'message' => 'Photo Not Removed!'	]);	
	}

	/**
	 * Suggest
	 *
	 * Validates and adds suggested photo to database.
	 * 
	 */
	public function suggest_post()
	{
		$input_url = $this->post('url');
		if (preg_match('/^.*((instagram.com|instagr.am)\/p\/[\w-]+\/?)$/i', trim($input_url), $result))
		{
			$response = $this->instagram_api->getMediaByURL($result[1]);
			if ($response->meta->code == 200)
			{
				$this->load->model('Photo_model');
				$add = $this->Photo_model->add_suggested_photo($response);
				if ($add['bool'])
				{
					$this->_send_response(['code' => 200, 
										'message' => $add['message']]);
				}
				$this->_send_response(['code' => 500, 
									'message' => $add['message']]);
			}
			else
			{
				$this->load->helper('send_error_mail');
				send_error_mail(__METHOD__, $response->meta->code, $response->meta->error_type, $response->meta->error_message);
				$this->_send_response(['code' => $response->meta->code, 
									'message' => $response->meta->error_message]);
			}
		}
		$this->_send_response(['code' => 404, 
							'message' => 'Invalid Instagram URL.']);
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