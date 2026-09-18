<?php
//$_SESSION['player_area'] = 'intro';
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <h3 style="font-weight:bold; margin-left:10px; margin-top:10px;">Prologue</h3>
    <div id="info-screen-text-home">
        <p>
            <strong>Tout est noir ... On n'entend rien.</strong> <br/><br/>
            Tout d'un coup, vous sentez de la chaleur, beaucoup de chaleur. <br/>
            Vous ouvrez les yeux. <br/>
            Vous êtes entouré de flammes ! <br/>
            Vous regardez autour de vous dans la panique, mais bizarrement, vous ne sentez pas de brûlure. <br/>
            Vous sentez aussi un grand vent, comme dans une tempête. <br/>
            Vous essayez de voir au travers des flammes ... Quelque chose de gigantesque se rapproche rapidement ..! <br/><br/>
            Vous vous écrasez avec fracas sur le sol. <br/>
            <br/>
            Vous ouvrez à nouveau les yeux. Vous n'avez rien de mal. <br/>
            Les flammes ont disparu, excepté sur quelques touffes d'herbe autour du cratère que vous avez formé. <br/>
            Il fait nuit. La lune éclaire fortement et on peut y voir assez loin. <br/>
            Devant vous, à distance, un haut mur se dresse et s'étend, à perte de vue, des deux côtés. <br/>
            Tout à coup, une boule de feu vous passe au-dessus de la tête, et s'écrase non loin. <br/>
            Vous courrez vers le crash ... Il y a quelqu'un dans le creux et l'herbe brûle tout autour. <br/>
            Vous regardez au ciel, et apercevez une montagne. La lumière ne vient pas de la lune, mais d'un brasier au sommet. Une pluie de météores tombe, qui viennent percuter le sol l'une après l'autre, tout autour de la montagne. <br/>
            <br/>
            Puis, tout se calme. Il y a encore des boules de feu dans le ciel, mais le gros est passé. <br/>
            L'autre se réveille, et vous vous regardez. <br/>
            Soudain, vous entendez d'effroyables bruits par-delà la muraille. Des craquements d'arbres, des chocs de rocks, des hurlements inhumains. <br/>
            Vous fuyez pour vous réfugier en hauteur, et rejoignez la ville abandonnée sur la montagne, comme toutes les autres créatures autour de vous. <br/>
            <br/>
            <span style="font-weight:bold;">Ici, il n'y a pas de héros. Tous sont perdus dans ce monde inconnu, peuplé de monstres, et où l'Ombre menace de tout engloutir.
            Mais une âme forte et ambitieuse peut s'élever rapidement, dans cette terre d'exploration, de trésors et d'opportunités.</span> <br/>
            <br/>
            Au matin, votre aventure commence ... <br/>
            <br/>
        </p>
        <a id="go-to-horlogis-page" href="index.php?page=horlogis" style="text-align:center;">Nous sommes venus dans un météore.</a>
	    <div><p><br><br></p></div>
    </div>

</div>

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>