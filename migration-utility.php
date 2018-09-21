<?php
require __DIR__ . '/src/bin/PrepareMigration.php';

$utility     = new PrepareMigration(__DIR__ . '/src/matrix/field-manager.json');
$systemError = $utility->getSystemCheckError();
$utility->generateMigration();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Matrix Migration Utility</title>
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

<div class="border m-6 rounded-lg bg-white mx-auto shadow-lg max-w-xs overflow-hidden">
	<img class="h-24 min-w-full block" src="https://png.pngtree.com/thumb_back/fh260/back_pic/00/02/62/305619b17d2530d.jpg" />
	<div class="px-4 py-3 relative min-h-3">
		<div class="mt-2 text-center sm:text-left sm:flex-grow">
			<div class="mb-4">
				<p class="text-xl font-bold leading-tight">Matrix Migration Tool</p>
				<p class="text-sm leading-tight text-grey-dark">generate a migration for field manager plugin</p>
			</div>
			<?php

			if($systemError === null): ?>

            <?php
                $notifications = $utility->getErrors();
                if(count($notifications)){
                    ?><div class="px-2 mb-3"><?php
                    foreach($notifications as $notification){
                        ?><p class="text-red text-bold"><?php echo $notification ?></p><?php
                    }
                    ?></div><?php
                }
            ?>

            <div>
                <form class="w-full max-w-xs" action="" method="post">

                    <p class="text-sm mb-2"><strong>Insert the name of the destination matrix:</strong></p>
                    <div class="flex flex-wrap -mx-3 mb-2">
                        <div class="w-full px-3">
                            <input class="block w-full bg-grey-lighter text-grey-darker border <?php echo $matrixNameError ? 'border-red' : 'border-grey-lighter' ?> rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-grey" type="text" name="matrixName" value="<?php echo $_POST['matrixName'] ?? '' ?>">
                        </div>
                    </div>

                    <p class="text-sm mb-6"><strong>Select the blocks that you want to keep:</strong></p>

                    <p class="mb-4">
                        <button type="button" onclick="checkCheckboxes()" class="bg-transparent hover:bg-blue text-blue-dark font-semibold hover:text-white p-1 border border-blue hover:border-transparent rounded">
                            Select all
                        </button>
                        <button type="button" onclick="uncheckCheckboxes()" class="bg-transparent hover:bg-blue text-blue-dark font-semibold hover:text-white p-1 border border-blue hover:border-transparent rounded">
                            Deselect all
                        </button>
                    </p>

                    <?php foreach($utility->getBlockList() as $item): ?>
                    <div class="md:flex md:items-center mb-2">
                        <div class="ml-2 "></div>
                        <label class="block">
                            <input class="mr-2 leading-tight checkbox" type="checkbox" name="<?php echo $item['key'] ?>" value="1" <?php if($utility->isCheckboxChecked($item['key'])) echo 'checked="checked"' ?>>
                            <span class="text-sm"><?php echo $item['name'] ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>

                    <div class="md:flex md:items-center mt-6">
                        <div class="">
                            <button class="shadow bg-purple hover:bg-purple-light focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded" type="submit">
                                Generate the migration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div>
                <p class="leading-tight text-red text-sm">
                    <?php echo $systemError ?>
                </p>
            </div>
            <?php endif; ?>
		</div>
	</div>
</div>

<script>
    function checkCheckboxes(source) {
        checkboxes = document.getElementsByClassName('checkbox');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = true;
        }
    }
    function uncheckCheckboxes(source) {
        checkboxes = document.getElementsByClassName('checkbox');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = false;
        }
    }
</script>
</body>
</html>