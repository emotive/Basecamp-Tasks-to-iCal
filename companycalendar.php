<?php

    
include_once "config.inc";


//make sure all the elements are there
if(!$_GET['site']) {
	$fail = TRUE;
	echo "Site is required.<br />
	This is the segment of your URL after http:// and before .	basecamphq.com/<br /><br />";
}
if(!$_GET['user']) {
	$fail = TRUE;
	echo "Username is required.<br /><br />";
}
if(!$_GET['password']) {
	$fail = TRUE;
	echo "Password is required.<br /><br />";
}
if($fail) {
	echo 'Proper URL structure is:<br />http://bctasks.hdev1.com/mytasks.php?site=<strong>SiteURLSegment</strong>&user=<strong>YourUsername</strong>&password=<strong>YourPassword</strong>';
	exit;
}

//first, figure out the URL
$sitename = $_GET['site'];
$url = 'https://'.$sitename.'.basecamphq.com/';

//then get the username
$username = $_GET['user'];

//and the password
$password = $_GET['password'];

// typical REST request
require('Basecamp.class.php');
$bc = new Basecamp($url,$username,$password, 'simplexml');

$response_people = $bc->getPeople();


    foreach($response_people['body']->person as $person)
    
    	if ($person->{'company-id'} == $company_id) {  



$response = $bc->getTodoListsForPerson($person->{'id'});
// see the XML output
//print_r($response['body']->{'todo-list'});

foreach($response['body']->{'todo-list'} as $list) {
	//print_r($list);
	$list_id = $list->id;
	$list_title = $list->name;
	//Check for short version of list title
	$list2 = explode('[',$list_title);
	if(count($list2) == 2) {
		$list3 = explode(']',$list2[1]);
		$list_title = $list3[0];
	}
	
	
	$project_id = $list->{'project-id'};
	
	//get the project name
	$proj = $bc->getProject($project_id);
	$project_name = $proj['body']->name;
	//Check for short version of project name
	$proj2 = explode('[',$project_name);
	if(count($proj2) == 2) {
		$proj3 = explode(']',$proj2[1]);
		$project_name = $proj3[0];
	}
	
	foreach($list->{'todo-items'}->{'todo-item'} as $item) {
		//print_r($item);
		$item_id = $item->id;
		$item_text = $item->content;
		$item_due = str_replace("Z","",str_replace(":","",str_replace("-","",$item->{'due-at'})));
		//Check for short version of project name
		$item2 = explode('[',$item_text);
		if(count($item2) == 2) {
			$item3 = explode(']',$item2[1]);
			$item_text = $item3[0];
		}
		//$the_list[] = $project_name . ' ' . $list_title . ' - ' . $item_text . '     (|'.$item_id.'|'.$list_id.'|'.$project_id.'|)';
		
		$the_list[] = $person->{'first-name'}.": ".$item_text . ' - ' . $list_title . '^'.$project_name.'^https://'.$sitename.'.basecamphq.com/todo_lists/'.$list_id.'^'.$item_due;
	}
}

}


sort($the_list);
?>
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
X-WR-CALNAME:Company Calendar
X-WR-TIMEZONE:US/Eastern
VERSION:2.0
<?php foreach($the_list as $item) { ?>
BEGIN:VEVENT
<? 
$pieces = explode("^", $item);
echo 'SUMMARY:'.$pieces[0]; // piece1
?>

<?php echo 'DESCRIPTION:'.$pieces[1]; // piece2 ?>

<?php echo 'URL:'.$pieces[2]; // piece2 ?>

<?php echo 'DTSTAMP:'.$pieces[3]; // piece2 ?>

DTSTART;VALUE=DATE:<?php echo substr($pieces[3], 0, 8) ?>

DTEND;VALUE=DATE:<?php echo (substr($pieces[3], 0, 8))+1 ?>

SEQUENCE:1
STATUS:CONFIRMED
CLASS:PUBLIC
END:VEVENT
<?php } ?>
END:VCALENDAR