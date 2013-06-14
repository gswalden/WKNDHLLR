<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Photo extends CI_Controller {

	public function index()
	{
		
	}

	public function suggest($input_url)
	{
		if (preg_match('/.*(instagram.com\/p\/([\w-]+))\/?/', $input_url, $result))
		{
			$response = $this->instagram_api->getMediaByURL($result[1]);
			if ($response->meta->code == 200)
			{
				$this->load->model('Photo_model');
				$add = $this->Photo_model->add_suggested_photo($response);
				if ($add['bool'])
					$this->session->set_flashdata('add_photo', 'Photo Added!');
				else
					$this->session->set_flashdata('add_photo', 'Photo Not Added!' . $add['message']);		
			}
			else
			{
				$this->load->helper('send_error_mail');
				send_error_mail(__METHOD__, $response->meta->code, $response->meta->error_type, $response->meta->error_message);
				return ['bool' => FALSE, 'message' => 'Error from Instagram API!'];
			}
		}
		return FALSE;
	}
}