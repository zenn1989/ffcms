<?php
header("Content-Type: text/html; charset=utf-8\n");
header("Cache-Control: no-cache, must-revalidate\n");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// e-z params
$dim = 150;         /* image displays proportionally within this square dimension ) */
$cols = 4;          /* thumbnails per row */
?>
<!DOCTYPE html>
<html>
<head>
    <title>browse file</title>
    <meta charset="utf-8">

    <style>
        html,
        body {padding:0; margin:0; background:black; }
        table {width:100%; border-spacing:15px; }
        td {text-align:center; padding:5px; background:#181818; }
        img {border:5px solid #303030; padding:0; verticle-align: middle;}
        img:hover { border-color:blue; cursor:pointer; }
    </style>

</head>


<body>

<table>

    <?php

    $dir = $engine->constant->root."/upload/images/";

    $dir = rtrim($dir, '/'); // the script will add the ending slash when appropriate

    $files = scandir($dir);

    $images = array();

    foreach($files as $file) {
        // its image?
        if( !preg_match('/\.(jpg|jpeg|png|gif)$/i', $file) )
            continue;
        $file_insystem = $dir . '/' . $file;
        $image_link = $engine->constant->url . "/upload/images/" . $file;

        $image_info = getimagesize($file_insystem);
        $_w = $image_info[0];
        $_h = $image_info[1];

        if( $_w > $_h ) {       // $a is the longer side and $b is the shorter side
            $a = $_w;
            $b = $_h;
        } else {
            $a = $_h;
            $b = $_w;
        }

        $pct = $b / $a;     // the shorter sides relationship to the longer side

        if( $a > $dim )
            $a = $dim;      // limit the longer side to the dimension specified

        $b = (int)($a * $pct);  // calculate the shorter side

        $width =    $_w > $_h ? $a : $b;
        $height =   $_w > $_h ? $b : $a;

        // produce an image tag
        $str = sprintf('<img src="%s" width="%d" height="%d" title="%s" alt="">',
            $image_link,
            $width,
            $height,
            $file
        );

        // save image tags in an array
        $images[] = str_replace("'", "\\'", $str); // an unescaped apostrophe would break js

    }

    $numRows = floor( count($images) / $cols );

    // if there are any images left over then add another row
    if( count($images) % $cols != 0 )
        $numRows++;


    // produce the correct number of table rows with empty cells
    for($i=0; $i<$numRows; $i++)
        echo "\t<tr>" . implode('', array_fill(0, $cols, '<td></td>')) . "</tr>\n\n";

    ?>
</table>


<script>

    // make a js array from the php array
    images = [
        <?php

        foreach( $images as $v)
            echo sprintf("\t'%s',\n", $v);

        ?>];

    tbl = document.getElementsByTagName('table')[0];

    td = tbl.getElementsByTagName('td');

    // fill the empty table cells with data
    for(var i=0; i < images.length; i++)
        td[i].innerHTML = images[i];


    // event handler to place clicked image into CKeditor
    tbl.onclick =

        function(e) {

            var tgt = e.target || event.srcElement,
                url;

            if( tgt.nodeName != 'IMG' )
                return;

            url = '<?php echo $engine->constant->url . "/upload/images";?>' + '/' + tgt.title;

            this.onclick = null;

            window.opener.CKEDITOR.tools.callFunction(<?php echo $_GET['CKEditorFuncNum']; ?>, url);

            window.close();
        }
</script>
</body>
</html>