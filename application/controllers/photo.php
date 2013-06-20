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
	 * @param string $id Instagram-assigned media_id
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
		$this->_send_response(['code' => 404,
							'message' => 'Photo Not Found!'	]);
		
	}

	/**
	 * Get Photos
	 *
	 * Returns a database result object containing photos.
	 * 
	 * @param int $limit   Max number of photos
	 * @param string $date Date to exclude from and later
	 */
	public function get_photos_get($limit = NULL, $date = NULL)
	{
		$date = new DateTime($date);
		$date = $date->format('Y-m-d H:i:s');
		$this->load->model('Photo_model');
		if ($limit && $date)
			$query = $this->Photo_model->get_photos($limit, $date);
		else	
			$query = $this->Photo_model->get_photos();
		if ($query->num_rows() > 0)
		{
			$this->_send_response([ 'code' => 200, 
									'data' => $query->result()	]); 
		}
		$this->_send_response(['code' => 404,
							'message' => 'No Photos Found!'	]);
	}

	/**
	 * Delete Photo
	 *
	 * Deletes database row for specified photo.
	 * 
	 * @param string $id Instagram-assigned media_id
	 */
	public function delete_photo_post()
	{
		$id = $this->post('id');
		$this->load->model('Photo_model');
		if ($this->Photo_model->delete_photo($id))
		{
			$this->_send_response([ 'code' => 200, 
									'message' => 'Photo Deleted!'	]); 
		}
		$this->_send_response(['code' => 404,
							'message' => 'Photo Not Found!'	]);
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

	/**
	 * Approve Suggestion
	 *
	 * Adds a suggestion photo to the main photo queue.
	 * 
	 */
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

	/**
	 * Remove Suggestion
	 *
	 * Deletes a suggested photo.
	 * 
	 */
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
		if (preg_match('/^.*((instagram.com|instagr.am)\/p\/[\w-]+\/?)$/i', trim($input_url), $regex))
		{
			$response = $this->instagram_api->getMediaByURL($regex[1]);
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