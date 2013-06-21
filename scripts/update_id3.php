<?php
die("This script can be used to update ID3 tags. You must remove this die() statement to run.");

$base = "/var/www/nas/www.example.com/htdocs/streams";

require_once("{$base}/lib/Config.php");
require_once("{$base}/lib/Auth.php");
require_once("{$base}/lib/Streams.php");
require_once("{$base}/lib/getid3/getid3/getid3.php");
foreach (glob("{$base}/lib/getid3/getid3/*.php") as $resource) {
    require_once($resource);
}

$getID3 = new getID3();
$getID3->setOption(array("encoding" => "UTF-8"));

if (isset($_SERVER['argv'][1])) {
    $files[] = $_SERVER['argv'][1];
} else {
    $files = glob("*.mp3");
}

$c = count($files);
foreach ($files as $k=>$f) {
    print("[$k of $c] Processing $f\n");
    $id3 = $getID3->analyze($f);

    // Set new ID3 tags
    unset($tagData);
    $tagData['title'] = array($id3['tags']['id3v2']['title'][0]);
    $tagData['artist'] = array($id3['tags']['id3v2']['artist'][0]);
    $tagData['album'] = array($id3['tags']['id3v2']['album'][0]);
    $tagData['genre'] = array($id3['tags']['id3v2']['genre'][0]);
    $tagData['track'] = array($id3['tags']['id3v2']['track_number'][0]);
    $tagData['year'] = array($id3['tags']['id3v2']['year'][0]);
    $tagData['comment'] = array("Ev1l Pr1sm");
    //var_dump($tagData);die();
    if (file_exists("cover.jpg")) {
        $tagData['attached_picture'][0]['data'] = file_get_contents("cover.jpg");
        $tagData['attached_picture'][0]['picturetypeid'] = "jpeg";
        $tagData['attached_picture'][0]['description'] = "cover.jpg";
        $tagData['attached_picture'][0]['mime'] = "image/jpeg";
    }

    $tagwriter = new getid3_writetags;
    $tagwriter->filename = $f;
    $tagwriter->tagformats = array("id3v2.3");//, "id3v2.4");
    $tagwriter->overwrite_tags = true;
    $tagwriter->tag_encoding = "UTF-8";
    $tagwriter->remove_other_tags = true;
    $tagwriter->tag_data = $tagData;

    if ($tagwriter->WriteTags()) {
        print("Successfully wrote tags\n");
        if (!empty($tagwriter->warnings)) {
            print("There were some warnings:" . implode("\n", $tagwriter->warnings) . "\n\n");
        }
    } else {
        print("There were some errors:" . implode("\n", $tagwriter->errors) . "\n\n");
    }
}
//var_dump($o);
