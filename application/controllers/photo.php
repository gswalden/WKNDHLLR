<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . "libraries/REST_Controller.php";

class Photo extends REST_Controller {

	public function see_get($id)
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

	public function tags_post()
	{
		$this->instagram_api->access_token = '387621951.14ddff3.4690ea24f5444c0fab9b0722b2c569c3';
		$post = $this->input->post();
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

	public function suggest_get()
	{
		$this->response(['msg' => 'yes!']);
	}

	public function suggest_post()
	{
		$this->instagram_api->access_token = '387621951.14ddff3.4690ea24f5444c0fab9b0722b2c569c3';
		$input_url = $this->input->post('url');
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
				// return ['bool' => FALSE, 'message' => 'Photo Not Added!'];		
			}
			else
			{
				$this->load->helper('send_error_mail');
				send_error_mail(__METHOD__, $response->meta->code, $response->meta->error_type, $response->meta->error_message);
				$this->_send_response(['code' => $response->meta->code, 
									'message' => $response->meta->error_message]);
				// return ['bool' => FALSE, 'message' => 'Error from Instagram API!'];
			}
		}
		$this->_send_response(['code' => 404, 
							'message' => 'Invalid Instagram URL.']);
		// return ['bool' => FALSE, 'message' => 'Invalid Instagram URL.'];
	}

	private function _send_response($data = NULL)
	{
		if ($data)
			$this->response($data, $data['code']);
		else
			$this->response(NULL);
	}
}