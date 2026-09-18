<!DOCTYPE html>
<?php 
$token = $_SESSION['cb_token'];
?>

<!-- 1 single chatbox test (not done)
<div id="chatboxes-wrapper">
    <div id="cb-header">
        <select id="cb-selector">
            <option value="cb-general" selected>Chat général</option>
            <option value="cb-local">Chat local</option>
        </select>
        <div id="cb-title">
            <span>Chat général</span>
        </div>
        <div class="clear-float"></div>
    </div>
    <div id="chatbox-body">
        <iframe src='http://localhost:3381/?param=<?php //echo $token; ?>' id="cb-general-iframe"></iframe>
        <iframe src='http://localhost:3381/localnsp?param=<?php //echo $token; ?>' id="cb-local-iframe" hidden></iframe>
    </div>
</div>
        -->

<div id="chatboxes-wrapper">
        <div id="left-cb">
            <div id="left-cb-title">
                <span>Chat général <small>(Clic pour réduire)</small></span>
            </div>
            <iframe src='http://localhost:3381/?param=<?php echo $token; ?>' id="cbleft-iframe"></iframe>
        </div>
        <div id="right-cb">
            <div id="right-cb-title">
                <span>Chat local <small>(Clic pour réduire)</small></span>
            </div>
            <iframe src='http://localhost:3381/localnsp?param=<?php echo $token; ?>' id="cbright-iframe"></iframe>
        </div>
        <div class="clear-float"></div>
</div>

      