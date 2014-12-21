<?php
//header('Access-Control-Allow-Origin: *');

$url = $_GET["Podurl"];

	$pod->pod = @file_get_contents($url);
	/*if(!$this->pod = @file_get_contents($url))
		{
		throw new Exception('Can\'t load podcast XML file');
		}*/
		
	$pod->pod = str_replace('itunes:', '', $pod->pod);
	$pod->pod = simplexml_load_string($pod->pod);

//$image = $pod->channel['itunes:summary']->image['url']; //GC channel image
//$title = $pod->channel->title; // podcast title
$main_poster = $pod->pod->channel->image['href'];
if(!$main_poster) 
$main_poster = $pod->pod->channel->image->url;
//echo $main_poster;

// get main chanel author
$main_author = $pod->pod->channel->author;

$items = $pod->pod->channel->item;


$i = 0;
$data = array();
foreach($items as $item) {  //loop over elements you want to return
	
	$datum = parse_url($item->enclosure['url']);
	$parts = pathinfo($datum['path']);
	$type = $parts['extension'];
	
	if($type=="mp4") $type = "m4v";
	if($type=="ogg") $type = "oga";
	//audio/mp3	
	
	//Get item poster
	$poster = $item->image['href'];
	if(!$poster) $poster = $main_poster;
	
	//Get item author
	$author = $item->author;
	if(!$author) $author = $main_author;
	
	$tmp = str_replace(" ","",$item->pubDate);
	$data[] = array(
        'title' => (string)$item->title,
        $type => (string)$item->enclosure['url'],//replace this with the XML elements you want to get
		'download' => (string)$item->enclosure['url'],
		//'poster' => (string)$res->image['href'],
		'poster' => (string)$poster,
		'artist' => (string)$author,
    );
	if (++$i == 20) break;// change this to the number of elements you want to get
}

array_multisort($data,SORT_DESC); //Order by date
// Order by date help: http://stackoverflow.com/questions/20662389/is-there-a-way-to-sort-by-pubdate-in-descending-order

$jsdata = ($_GET['callback'].'('.json_encode($data).');');
echo htmlspecialchars($jsdata, ENT_NOQUOTES, 'utf-8'); // return JSON wrapped in callback
?>