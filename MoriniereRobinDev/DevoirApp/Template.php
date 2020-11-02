<!DOCTYPE html>
<html lang="fr">
<head>
	<title><?php echo $title ?></title>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="DevoirApp/Model/skin/template.css" />
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
                <!--<li><a id="signIn" href="index.php?obj=pdf">Sign In</a></li>-->
            </ul>
        </nav>
    </header>
    
	<main>
		<h1><?php echo $title; ?></h1>
        
        <?php echo $feedback; ?>
            
		<?php echo $content; ?>
		
	</main>
</body>
</html>
