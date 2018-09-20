<?php

class PrepareMigration
{
	public $sourceFilePath = __DIR__ . '/../matrix/field-manager.json';
	private $sourceJson;
	private $errors = [];

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

	public function generateMigration()
	{
		if(isset($_POST) && count($_POST)) {
			if(isset($_POST['matrixName']) && $_POST['matrixName'] == ''){
				$this->addError("Please insert a valid name for the destination matrix");
				return;
			}

			$matrixName = $_POST['matrixName'];
			$matrixHandle = $this->slugify($_POST['matrixName']);

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

			$this->generateDestinationFileMigration($jsonMaster, $matrixHandle);
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
	
	public function aa()
	{

var_dump(count($argv));

if(count($argv) <= 2){
	echo "\nError: the name of the source json file or the name of the block to keep is missing from command.\n";
	echo "\nExample: prepare-single-block-matrix.php jsonfilenamepath.json \"Text and image\"";
	echo "\nYou can require multiple blocks like that:";
	echo "\nprepare-single-block-matrix.php jsonfilenamepath.json \"Text and image\" \"Iframe\" \"Quote\"";
	die();
}

if(is_file($argv[1])){
	$sourceJson = json_decode(file_get_contents($argv[1]), true);
}
else {
	echo "\nError: the file {$argv[1]} does not exist";
	die();
}

if(isset($sourceJson[0]['settings']['blockTypes']) === false){
	echo "\nError: The array containing the blockTypes is missing from the migration origin file";
	die;
}

$blocksToKeep = $argv;

// Keep only the arguments that specify the blocks to keep, remove everything else
array_splice($arguments, 0, 2);

$keysToKeep = [];

foreach($sourceJson[0]['settings']['blockTypes'] as $key => $item) {
	if(in_array($item['name'], $blocksToKeep)){
		unset($json[0]['settings']['blockTypes'][$key]);
	}
}

$jsone = preg_replace_callback ('/^ +/m', function ($m) {
	return str_repeat (' ', strlen ($m[0]) / 2);
}, json_encode ($json, JSON_PRETTY_PRINT));
file_put_contents($compiled, $jsone);



/*// compare two files line by line
$diff = Diff::compareFiles($source, $compiled);
echo '<h1>First diff:</h1>';
var_dump($diff);

echo '<h1>Second diff:</h1>';
// compare two files character by character
$diff = Diff::compareFiles($source, $compiled, true);*/



//echo '<pre>';var_dump($json[0]['settings']['blockTypes']);echo '</pre>';
//var_dump($json->settings->blockTypes->new2);
	}
}