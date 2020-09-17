<?php  

function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//echo 'User Real IP - '.getUserIpAddr();

$ipad = getUserIpAddr();
//echo $ipad;

$res = file_get_contents('https://www.iplocate.io/api/lookup/'.$ipad.'');
$res = json_decode($res);

//echo $res->country; // United States
//echo $res->continent; // North America
//echo $res->latitude; // 37.751
//echo $res->longitude; // -97.822
$center_lat = $res->latitude;
$center_lng = $res->longitude;

$radius = '50000';
// Opens a connection to a mySQL server
$connection=mysql_connect ('localhost', 'user', 'password');
if (!$connection) {
  die("Not connected : " . mysql_error());
}
// Set the active mySQL database
$db_selected = mysql_select_db('database', $connection);
if (!$db_selected) {
  die ("Can\'t use db : " . mysql_error());
}
// Search the rows in the markers table, below are the fields for my table, edit to your liking
$query = sprintf("SELECT title, image, Fullname, Email, Address,amount, lat, lng,id, ( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * 
cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM jobs WHERE Status='verified'  HAVING distance < '%s' ORDER BY distance ASC LIMIT 0 , 20",
  mysql_real_escape_string($center_lat),
  mysql_real_escape_string($center_lng),
  mysql_real_escape_string($center_lat),
  mysql_real_escape_string($radius));
$result = mysql_query($query);
if (!$result) {
  die("Invalid query: " . mysql_error());
}

// Iterate through the rows, adding XML nodes for each
echo '<div class="list-block media-list"><ul>';
while ($row = @mysql_fetch_assoc($result)){
  
  
  //apply.php?id='.$row['id'].'
  echo'<li><a href="apply.php?id='.$row['id'].'"  class="external item-link item-content">';
	echo'<div class="item-media">';
	if (!empty($row['Avatar']))
	{
	echo '<img width="60px" src="'.$row['Avatar'].'" /></div>';

	}
	else
	{
		echo '<img src="portfolio.png" /></div>';
	}
		echo'<div class="item-inner"> <div class="item-title-row"> <div class="item-title">Project : ';
	echo ''.$row['title'].'';
	echo'</div><div class="item-after"></div> </div> <div class="item-subtitle">';
	echo''.$row['job'].''; echo '<span class="mined"> (N';echo''.$row['amount'].'';echo')</span>  ';  echo'<br /> I am ';echo round($row['distance'],1);echo ' km away from you';
	echo '</div> <div class="item-text"></div>   </div>   </a></li>';
  
}

echo'</ul></div>';




?>           
