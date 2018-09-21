<?php

class PrepareMigration
{
	public $sourceFilePath;
	private $sourceJson;
	private $errors = [];

	public function __construct($sourcePath)
	{
		$this->sourceFilePath = $sourcePath;
	}

	public function getSystemCheckError()
	{
		$error = null;

		if($this->sourceFileExists()){
			$this->sourceJson = $this->getSourceJson();

			if(!isset($this->sourceJson[0]['settings']['blockTypes'])){
				$error = "Missing array [0]['settings']['blockTypes'] from source json";
			}
		}
		else {
			$error = "Warning: source file does not exist at path {$this->sourceFilePath}";
		}

		return $error;
	}
	
	private function sourceFileExists(): bool
	{
		if(!is_file($this->sourceFilePath)){
			return false;
		}
		
		return true;
	}

	private function getSourceJson()
	{
		return json_decode(file_get_contents($this->sourceFilePath), true);
	}

	public function getBlockList()
	{
		$blocksArray = [];

		foreach($this->sourceJson[0]['settings']['blockTypes'] as $key => $item) {
			$blocksArray[] = [
				'name' => $item['name'],
				'key' => $key
				];
		}
		return $blocksArray;
	}

	public function isCheckboxChecked($key)
	{
		if(isset($_POST[$key]) && $_POST[$key] === "1"){
			return true;
		}
		return false;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	private function addError($error)
	{
		$this->errors[] = $error;
	}

	private function postDataIsValid()
	{
		$isValid = true;

		if($this->isFormSubmitted())
		{
			if (isset($_POST['matrixName']) && $_POST['matrixName'] == '')
			{
				$this->addError("Please insert a valid name for the destination matrix");

				$isValid = false;
			}

			if (isset($_POST['matrixHandle']) && $_POST['matrixHandle'] == '')
			{
				$this->addError("Please insert a valid handle");

				$isValid = false;
			}
		}

		return $isValid;
	}

	public function isFormSubmitted()
	{
		return (isset($_POST['submitted']) && $_POST['submitted'] === "true");
	}

	public function generateMigration()
	{
		if($this->postDataIsValid()){
			$matrixName = $_POST['matrixName'];
			$matrixHandle = $_POST['matrixHandle'];

			$jsonMaster = $this->sourceJson;

			$jsonMaster[0]['name'] = $matrixName;
			$jsonMaster[0]['handle'] = $matrixHandle;

			$atLeastOneSelected = false;

			foreach($jsonMaster[0]['settings']['blockTypes'] as $key => $item) {
				if(isset($_POST[$key]) === false){
					unset($jsonMaster[0]['settings']['blockTypes'][$key]);
				}
				else {
					$atLeastOneSelected = true;
				}
			}

			if($atLeastOneSelected === false){
				$this->addError("You should select at least one block to keep in the matrix migration");
				return;
			}

			$this->generateDestinationFileMigration($jsonMaster, $this->slugify($matrixName));
		}

	}

	private function generateDestinationFileMigration($json, $fileName)
	{
		$content = preg_replace_callback ('/^ +/m', function ($m) {
			return str_repeat (' ', strlen ($m[0]) / 2);
		}, json_encode ($json, JSON_PRETTY_PRINT));

		header('Content-Description: File Transfer');
		header('Content-Type: application/json');
		header("Content-disposition: attachment; filename=$fileName.json");
		echo $content;
		exit;
	}

	private function slugify($text)
	{
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
	
}