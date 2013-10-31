<?
/**
Ok... so... for the record, I know this backend is kind of terrible. I apologize, but 
I just wanted to get something working to demonstrate the ember.js frontend, which is really
where all the action is. 

Presumably, if you were building a "real" ember project, you'd use a "real" backend,
not something like this. 

 - jon

**/


include('rest_utils.php');
include('../config.php');

$data = RestUtils::processRequest();
// var_dump($data);
switch($data->getMethod() )
{
	case 'get':
		// retrieve a list of users

		//echo $_SERVER["REQUEST_URI"];
		$dataArray = parseDatasource($_SERVER["REQUEST_URI"]);
		//print_r($dataArray);

		if ($dataArray[0] == 'stories' && isset($dataArray[1]))
		{
			$outputArray = getSpecificStory($dataArray[1]);
			
		}
		elseif ($dataArray[0] == 'stories?latest=true') {
			$outputArray = getNewStories();
		}
		elseif (stristr($dataArray[0], 'getByTopicId') !== false) {

			$lookupId = str_replace('stories?getbytopicid=', '', $dataArray[0]);
			$outputArray = getStoriesByTopic($lookupId);
		}


		elseif ($dataArray[0] == 'topics' && isset($dataArray[1]))
		{
			$outputArray = getTopicById($dataArray[1]);
		}
		elseif ($dataArray[0] == 'topics')
		{
			$outputArray = getTopics();
		}

		echo json_encode($outputArray);
		die();
	case 'post':

		$rawBody = file_get_contents('php://input','r');
		//echo 'rawBody='.$rawBody;
		$putData = json_decode($rawBody)->story;

		$putData->title = addslashes($putData->title);
		$putData->creationDatetime = addslashes($putData->creationDatetime);
		$putData->body = addslashes($putData->body);

		$mysql_link = mysqlConnectionThing::returnConnection();

		$sql = "INSERT INTO stories ";
		$sql .= " (title, creationDatetime, body) ";
		$sql .= " VALUES ";
		$sql .= " ( '".$putData->title."', ";
		$sql .= " '".$putData->creationDatetime."', ";
		$sql .= " '".$putData->body."') ";
		$result=mysql_query($sql, $mysql_link); 
		if (!$result)
		{
			echo 'mysql error:'.mysql_error();
		}

		$outputArray = getSpecificStory(mysql_insert_id());

		echo json_encode($outputArray);
		break;
	case 'put':
		$dataArray = parseDatasource($_SERVER["REQUEST_URI"]);
		
		if ($dataArray[0] == 'stories' && isset($dataArray[1]))
		{
			$rawBody = file_get_contents('php://input','r');
			$putData = json_decode($rawBody)->story;

			$putData->title = addslashes($putData->title);
			$putData->creationDatetime = addslashes($putData->creationDatetime);
			$putData->body = addslashes($putData->body);

			$id = addslashes($dataArray[1]);

			$mysql_link = mysqlConnectionThing::returnConnection();
			$sql = "UPDATE stories SET ";
			$sql .= " title = '".$putData->title."', ";
			$sql .= " creationDatetime = '".$putData->creationDatetime."', ";
			$sql .= " body = '".$putData->body."' ";
			$sql .= " WHERE pn_sid = ".$id;

			$result=mysql_query($sql, $mysql_link); 
			if (!$result)
			{
				echo 'mysql error:'.mysql_error();
			}
		}


		die();
	// etc, etc, etc...
}


function getTopics()
{
	$mysql_link = mysqlConnectionThing::returnConnection();

	$outputArray = new stdClass();

	$outputArray->topics  = array();

	$sql = "SELECT * ";
	$sql .= " FROM topics ";
	$sql .= " ORDER BY topics.topicText ASC ";
	$result=mysql_query($sql, $mysql_link); 
	while ($query_data = mysql_fetch_array($result))
	{
		$tmpArray = array();
		$tmpArray['id'] = (int)$query_data['id'];
		$tmpArray['topicText'] = $query_data['topicText'];

		$outputArray->topics[] = $tmpArray;
	}

	return $outputArray;
}


function getTopicById($topic_id)
{
	$mysql_link = mysqlConnectionThing::returnConnection();

	$topic_id = addslashes($topic_id);

	$outputArray = new stdClass();

	$outputArray->topics  = array();

	$sql = "SELECT count(stories.pn_sid) as counter, stories.topicId, topics.pn_topictext ";
	$sql .= " FROM stories, topics ";
	$sql .= " WHERE stories.topicId = topics.id ";
	$sql .= " AND stories.topicId = ".$topic_id;
	$sql .= " GROUP BY stories.topicId";
	$result=mysql_query($sql, $mysql_link); 
	while ($query_data = mysql_fetch_array($result))
	{
		$tmpArray = array();
		$tmpArray['counter'] = $query_data['counter'];
		$tmpArray['id'] = (int)$query_data['id'];
		$tmpArray['topicText'] = $query_data['topicText'];

		$outputArray->topics[] = $tmpArray;
	}

	return $outputArray;
}

function getStoriesByTopic($topic_id)
{
	$mysql_link = mysqlConnectionThing::returnConnection();

	$topic_id = addslashes($topic_id);

	$outputArray = new stdClass();

	$outputArray->stories  = array();

	$sql = "SELECT stories.* ";
	$sql .= " FROM stories ";
	$sql .= " WHERE  stories.topicId= ".$topic_id;
	$sql .= " ORDER BY stories.pn_time DESC ";

	$result=mysql_query($sql, $mysql_link); 
	while ($query_data = mysql_fetch_array($result))
	{
		$tmpArray = new stdClass();
		$tmpArray->id = (int)$query_data['id'];
		$tmpArray->title = $query_data['title'];
		$tmpArray->creationDatetime = $query_data['creationDatetime'];
		$tmpArray->body = $query_data['body'];
		$tmpArray->topicId = $query_data['topicId'];

		$outputArray->stories[] = $tmpArray;
	}

	return $outputArray;
}


function getNewStories()
{
	$mysql_link = mysqlConnectionThing::returnConnection();

	$outputArray = new stdClass();

	$outputArray->stories  = array();

	$sql = " SELECT * ";
	$sql .= " FROM stories ";
	$sql .= " ORDER BY creationDatetime DESC ";
	$sql .= " LIMIT 5 ";

	$result = mysql_query($sql, $mysql_link); 
	while ($query_data = mysql_fetch_array($result))
	{
		$tmpArray = new stdClass();
		$tmpArray->id = (int)$query_data['id'];
		$tmpArray->title = $query_data['title'];
		$tmpArray->creationDatetime = $query_data['creationDatetime'];
		$tmpArray->body = $query_data['body'];
		$tmpArray->topicId = $query_data['topicId'];

		$outputArray->stories[] = $tmpArray;
	}

	return $outputArray;
}



function getSpecificStory($id)
{
	$mysql_link = mysqlConnectionThing::returnConnection();

	$outputArray = new stdClass();

	$sql = " SELECT * ";
	$sql .= " FROM stories ";
	if ($pn_sid == -1)
	{
		$sql .= " ORDER BY pn_time DESC ";
		$sql .= " LIMIT 1 ";
	}
	else
	{
		$id = addslashes($id);
		$sql .= " WHERE id = '".$id."'";
	}


	$result = mysql_query($sql, $mysql_link); 
	while ($query_data = mysql_fetch_array($result))
	{
		$tmpArray = new stdClass();
		$tmpArray->id = (int)$query_data['id'];
		$tmpArray->title = $query_data['title'];
		$tmpArray->creationDatetime = $query_data['creationDatetime'];
		$tmpArray->body = $query_data['body'];
		$tmpArray->topicId = $query_data['topicId'];
	}
	
	$outputArray->story  = $tmpArray;

	return $outputArray;
}


function parseDatasource($inputString)
{
	$dataArray = explode('/', $inputString);
	$dataArray[2] = strtolower($dataArray[2]);

	return array_slice($dataArray,2);
}