<?php
require_once __DIR__ . '/../vendor/autoload.php';
abstract class HTTP_TestCase extends PHPUnit_Extensions_Database_TestCase {

	public function get($url) {
		$ch = curl_init($url);
		return $this->execCurl($ch);
	}

	public function post($url, $vars, $key = null) {
		$data_string = json_encode($vars);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		$header = array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string),
		);
		/*
			if ($key) {
				$header[] = 'Authorization: Bearer asdfsdfsdf';
		*/

		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		return $this->execCurl($ch);
	}

	protected function execCurl($ch) {
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$out = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->assertEquals(0, curl_errno($ch), curl_error($ch));
		$this->assertEquals(200, $code, 'HTTP code ' . $code . ' not 200!');
		curl_close($ch);
		$d = json_decode($out);
		$this->assertNotNull($d, 'Error decoding json response. ' . $out);
		return $d;
	}
}
