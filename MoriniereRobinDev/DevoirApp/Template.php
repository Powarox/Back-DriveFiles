<!DOCTYPE html>
<html lang="fr">
<head>
	<title><?php echo $title ?></title>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="DevoirApp/Model/Skin/Template.css"/>
	<link rel="stylesheet" href="DevoirApp/Model/Skin/Responsive.css"/>
	<script defer src="DevoirApp/Model/Js/Upload.js"></script>
</head>
<body>
    <header>
        <nav class="menu">
            <ul>
                <?php
                    foreach ($menu as $text => $link) {
                        echo "<li><a href=\"$link\">$text</a></li>";
                    }
                ?>
            </ul>
        </nav>
    </header>

	<main>
		<h1><?php echo $title; ?></h1>

        <?php echo $feedback; ?>

		<?php echo $content; ?>
	</main>

	<footer>
		<p>Copyrite Moriniere Robin 21606393 - Mohamed Lamine Seck 21711412</p>
	</footer>
</body>
</html>
