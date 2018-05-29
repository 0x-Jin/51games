<?php
class CorefireWxPayException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
