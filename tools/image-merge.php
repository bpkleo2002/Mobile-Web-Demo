<?php
function mergeImages() {
    // Build an array of the uploaded files
    $src = Array();
    $ext = Array();
    for ($i = 0; $i < count($_FILES); $i++) {
        $tmpname = $_FILES['image' . $i]['tmp_name'];
        if (strlen($tmpname) > 0) {
            array_push($src, $tmpname);
            array_push($ext, pathinfo($_FILES['image' . $i]['name'], PATHINFO_EXTENSION));
        }
    }

    // Error checking
    if (count($src)==0) {
        return 'You must specify at least one image file!';
    }

    // Retrieve icon size, the size or the individual images
    $iconWidth = intval($_POST['width']);
    $iconHeight = intval($_POST['height']);

    // Error checking
    if ($iconWidth<=0 || $iconHeight<=0) {
        return 'You must specify a width and height of the individual icons in the merged image!';
    }

    // Create image to hold the merged images
	$mergedImage = imagecreatetruecolor($iconWidth * count($src), $iconHeight);
    if ($_POST['format']=="png") {
        $alphacolor = imagecolorallocatealpha($mergedImage, 0, 0, 0, 127);
        imagecolortransparent($mergedImage, $alphacolor);
        imagealphablending($mergedImage, false);
        imagesavealpha($mergedImage, true);
        imagefilledrectangle($mergedImage, 0, 0, $iconWidth * count($src), $iconHeight, $alphacolor);
    }
    else {
        $bkgndcolor = imagecolorallocate($mergedImage, 255, 255, 255);
        imagefilledrectangle($mergedImage, 0, 0, $iconWidth * count($src), $iconHeight, $bkgndcolor);
    }

    // Open all images
    for ($i = 0; $i < count($src); $i++) {
        if ($ext[$i]=='jpg' || $ext[$i]=='jpeg')
            $image = imagecreatefromjpeg($src[$i]);
        else if ($ext[$i]=='png')
            $image = imagecreatefrompng($src[$i]);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $scaleX = $imageWidth / $iconWidth;
        $scaleY = $imageHeight / $iconHeight;
        if ($_POST['resize']=='in') {
            if ($scaleX > $scaleY) {
                $scale = $scaleY;
                imagecopyresampled($mergedImage, $image, $i * $iconWidth, 0, ($imageWidth - $iconWidth*$scale)/2, 0, $iconWidth, $iconHeight, $iconWidth*$scale, $iconHeight*$scale);
            }
            else {
                $scale = $scaleX;
                imagecopyresampled($mergedImage, $image, $i * $iconWidth, 0, 0, ($imageHeight - $iconHeight*$scale)/2, $iconWidth, $iconHeight, $iconWidth*$scale, $iconHeight*$scale);
            }
        }
        else {
            if ($scaleX > $scaleY) {
                $scale = $scaleX;
                imagecopyresampled($mergedImage, $image, $i * $iconWidth, ($iconHeight - $imageHeight/$scale)/2, 0, 0, $iconWidth, $imageHeight/$scale, $imageWidth, $imageHeight);
            }
            else {
                $scale = $scaleY;
                imagecopyresampled($mergedImage, $image, $i * $iconWidth + ($iconWidth - $imageWidth/$scale)/2, 0, 0, 0, $imageWidth/$scale, $iconHeight, $imageWidth, $imageHeight);
            }
        }
    }
    
    $output = uniqid();
    if ($_POST['format']=="png") {
        $output = 'merge-' . $output . '.png';
        imagepng($mergedImage, '../_temp/' . $output, 0);
    }
    else {
        $output = 'merge-' . $output . '.jpg';
        imagejpeg($mergedImage, '../_temp/' . $output, 95);
    }

    header('Location: image-merge.php?image=' . $output . '&width=' . ($iconWidth * count($src)) . '&height=' . $iconHeight);
    die();
}

$errmsg = NULL;
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $errmsg = mergeImages();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Merge Images</title>
    <meta name="viewport" content="user-scalable=yes, width=device-width" />
    <link rel="stylesheet" href="../style.css" type="text/css" />
</head>
<body>
	<h1>Merge Images <a href="index.html">Back</a></h1>
<?php
if ($errmsg!=NULL) {
    ?>
    <dl>
        <dt>Error</dt>
        <dd><?php echo $errmsg; ?></dd>
    </dl>
    <?php
}
else if (!isset($_GET['image'])) {
    ?>
	<dl>
		<dt>Upload images</dt>
		<dd>
            <form method="POST" enctype="multipart/form-data">
                <p>
                    <input type="file" name="image0" /><br />
                    <input type="file" name="image1" /><br />
                    <input type="file" name="image2" /><br />
                    <input type="file" name="image3" /><br />
                    <input type="file" name="image4" /><br />
                    <input type="file" name="image5" /><br />
                    <input type="file" name="image6" /><br />
                    <input type="file" name="image7" /><br />
                    <input type="file" name="image8" /><br />
                    <input type="file" name="image9" /><br />
                </p>
                <p>
                    Size all images to icon size:<br />
                    <input type="text" name="width" size="5" value="50" /> x 
                    <input type="text" name="height" size="5" value="50" />
                </p>
                <p>
                    Resizing strategy:<br />
                    <input type="radio" id="resizeout" name="resize" value="out" checked="checked" />
                    <label for="resizeout">Zoom out, adding empty area</label><br />
                    <input type="radio" id="resizein" name="resize" value="in" />
                    <label for="resizein">Zoom in, accepting cutting off area</label>
                </p>
                <p>
                    Output image format:<br />
                    <input type="radio" id="formatpng" name="format" value="png" checked="checked" />
                    <label for="formatpng">png</label><br />
                    <input type="radio" id="formatjpg" name="format" value="jpg" />
                    <label for="formatjpg">jpg</label>
                </p>
                <p>
                    <input type="submit" value="Upload" />
                </p>
            </form>
        </dd>
	</dl>
    <?php
}
else {
    ?>
    <dl>
        <dt>Merged image <?php echo  $_GET['width'] . ' x ' . $_GET['height']; ?></dt>
        <dd>
            <img src="../_temp/<?php echo $_GET['image']; ?>" />
        </dd>
    </dl>
    <?php
}
?>
</body>
</html>
