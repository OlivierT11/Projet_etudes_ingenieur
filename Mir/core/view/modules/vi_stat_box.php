<!DOCTYPE html>
<?php 
include(dirname(dirname(dirname(__FILE__)))."/control/modules/ctrl_stat_box.php");
?>
<div id="stat-box-wrapper">
    <div id="stat-box">
        <ul>
            <?php echo $getStatBoxListHTML; ?>
        </ul>
    </div>
</div>