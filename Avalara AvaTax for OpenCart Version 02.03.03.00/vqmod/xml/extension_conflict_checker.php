<!DOCTYPE HTML>
<html>
<head>
  <meta charset="UTF-8">
  <title>Extension Conflict Checker</title>
  <meta name="description" content="Extension Conflict Checker for OpenCart">
  <meta name="keywords" content="keyword1, keyword2, keyword3">
  <link href="Avalara/css/style.css" rel="stylesheet" type="text/css">
</head>
    <body>
        <div id="main-wraper">
            <div id="top-wraper">
                <div id="banner">Extension Conflict Checker
                    <div id='button'>
                        <a href=<?php echo "../".basename(dirname(__FILE__))."/Avalara/examples/check_avataxes.php"?> title="Download PDF" target="_blank" class="link">Download as PDF</a>
                    </div>
                </div>

            </div>
            <div id="mid-wraper">
                <div id="mid-wraper-inner">
                <?php include_once "check_avataxes.php";
                $tp = new check_vqmod_xml();
                $tp->retrieve_nodes();
                $tp->compare_file_paths();
                ?>
                <!--div id="right-wraper">

                <div class="right-part" style="padding: 10px 0px;">
                <div style="width: 290px; float: left;">
                </div>

                <p
                 style="margin: 0px; padding: 0px; width: 200px; float: right; text-align: right; display: block;">&copy;
                <!--DO NOT Remove The Footer Links>
                Designed by <a class="footer-link" target="_blank"
                 href="http://www.htmltemplates.net/">htmltemplates.net</a></p>
                <!--Designed by><a href="http://www.htmltemplates.net">
                <img src="Avalara/images/footnote.gif" class="copyright" alt="html templates"></a>
                <!--In partnership with><a href="http://websitetemplates.net">
                <img src="Avalara/images/footnote.gif" class="copyright" alt="website templates"></a>
                <!--DO NOT Remove The Footer Links-->
                </div>
            </div>
        </div>
    </body>
</html>
