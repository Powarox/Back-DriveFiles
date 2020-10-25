<!DOCTYPE html>
<html lang="fr">
<head>
	<title><?php echo $title ?></title>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="DevoirApp/Model/skin/template.css" />
</head>
<body>
    <nav class="menu">
		<ul>
            <?php
                foreach ($menu as $text => $link) {
                    echo "<li><a href=\"$link\">$text</a></li>";
                }
            ?>
		</ul>
	</nav>
	<main>
		<h1><?php echo $title; ?></h1>
        
        <?php echo $feedback; ?>
            
		<?php echo $content; ?>
	</main>
</body>
</html>
