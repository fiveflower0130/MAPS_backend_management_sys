<?php
	session_start();
	header("Content-Type: text/html; charset=utf-8");
	include("src/dbconnect/connCMS.php");
	
	$devicename = $_GET['id'];
	$apptoken = $_GET['token'];//app_token取得
	$portalUrl = 'http://10.63.3.159:8080/axis2/services/ManagerPortal?wsdl';
	
	if(isset($_POST["action"])&&($_POST["action"]=="push"))
	{
		$appFans = $_POST["push-points"];//關注度取得
		$pushmessage = $_POST["PushMessage"];//push的訊息
		$pushlink = $_POST["PushLink"];//push的連結	
		
		//call protol web service
		$client = new SoapClient($portalUrl);						
		$result = $client ->getMapsManager(array("return"));

		$json = json_decode($result->return); //抓取result的json物件內容
		$serviceUrl = $json->{"serviceUrl"};
		$getMapsManager = $json->{"msgCode"};	
			
		if($getMapsManager == 0)
		{
			try
			{
				$opts = array('http'=>array('user_agent' => 'PHPSoapClient'));
				$context = stream_context_create($opts);
				//抓取web service的wsdl
				$client = new SoapClient($serviceUrl,
                             array('stream_context' => $context,
                                   'cache_wsdl' => WSDL_CACHE_NONE));
								   
				//執行push notification並將參數輸入
				$result = $client ->pushNotificationByAttentionNumber(array(
					"appToken" => $apptoken,
					"threshold" => $appFans, 
					"message" => $pushmessage, 
					"link" => $pushlink
				));
			
		    	//print_r($result);
				//print_r($result->return);
				$json = json_decode($result->return); //抓取result的json物件內容
				$data = $json->{"Data"};//抓取抓取物件內訊息名稱Data
				$msgcode = $json->{"msgCode"};//抓取抓取物件內訊息名稱msgcode
				
				echo "<script>";
				echo "alert('".$msgcode."')";
				echo "</script>";
				
				switch($msgcode){
					case 0:
						echo "<script>";
						echo "alert('推播成功')";
						echo "</script>";
					break;
					case 404:
						echo "<script>";
						echo "alert('推播失敗，請確認填入資料是否正確')";
						echo "</script>";
					break;
					case 412:
						echo "<script>";
						echo "alert('推播失敗，請確認填入資料是否正確')";
						echo "</script>";
					break;
					case 500:
						echo "<script>";
						echo "alert('推播失敗，系統錯誤請重新輸入')";
						echo "</script>";
					break;
					case 503:
						echo "<script>";
						echo "alert('推播失敗，請確認網路連線是否穩定')";
						echo "</script>";
					break;
				}
			}
			catch(Exception $e){
				echo $e->getMessage();
			}
		}
		else{
			switch($getMapsManager){
				case 500:
					echo "<script>";
					echo "alert('系統錯誤 請重新輸入')";
					echo "</script>";
				break;
				case 503:
					echo "<script>";
					echo "alert('資料庫處理錯誤 請重新輸入')";
					echo "</script>";
				break;
				case 404:
					echo "<script>";
					echo "alert('系統錯誤 請重新輸入')";
					echo "</script>";
				break;
				case 412:
					echo "<script>";
					echo "alert('系統錯誤 請重新輸入')";
					echo "</script>";
				break;
			}
		}
			
	}
	
	$sql_query = "SELECT * FROM `".$devicename."`";
	$result = mysql_query($sql_query);
	$total_records = mysql_num_rows($result);
?>
<!DOCTYPE html>
<html lang="zh-tw">
<head>
<title>Maps CMS App</title>
	<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/><!-- 讓瀏覽器的網址和選單隱藏 -->
	
	<link href="/css/jquery.mobile-1.4.5.min.css" rel="stylesheet">
	<!-- <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css" /> -->
	<!-- Graphic -->
	<!-- <link href="/css/morris.css" rel="stylesheet"> -->
	
	<!-- jqplot graphic -->
	<link href="/css/jquery.jqplot.min.css" rel="stylesheet">
	 
    <!-- Custom styles for this template -->
	<link href="/css/backend/custom.css" rel="stylesheet">

	<script type="text/javascript" src="/js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="/js/jquery.mobile-1.4.5.min.js"></script>
	
	<!-- jquery graphic -->
	<!--<script type="text/javascript" src="/js/raphael-min.js"></script>
	<script type="text/javascript" src="/js/morris.min.js"></script>-->
	
	<!-- jquery jqplot graphic>-->
	<script type="text/javascript" src="/js/jquery.jqplot.min.js"></script>
	<script type="text/javascript" src="/js/plugins/jqplot.pieRenderer.min.js"></script>
	<script type="text/javascript" src="/js/plugins/jqplot.donutRenderer.min.js"></script>
	
	
	<!-- jquery other tool -->
	<script type="text/javascript" src="/js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="/js/messages_zh_TW.min.js"></script> 	
	<script type="text/javascript" src="/js/backend/config.js"></script>
	<script type="text/javascript" src="/js/backend/push_single.js"></script>
	
</head>
<body>  
  <div data-role="page" id="Maps-App-Push-Fans">
	<div data-role="header" data-position="fixed" data-theme="b">
		<h2>發送訊息</h2>
		<?php
			//echo'<a href="#" data-role="button" data-icon="back" class="ui-btn-left" data-corners="true"  data-transition="slide" data-direction="reverse"  data-rel="back">回上一頁</a>';
			echo"<a href='Maps_App_Data.php?id=".$devicename."&token=".$apptoken."' class='ui-btn-left' data-transition='slide' data-icon='back' data-direction='reverse' data-corners='true'>回上一頁</a>";
		?>
		<div data-role="navbar">
			<ul>
			<?php
				echo"<li><a href='Maps_App_Push_All.php?id=".$devicename."&token=".$apptoken."' data-transition='none' data-theme='a'>全體推波</a></li>";
				echo"<li><a href='#' class='ui-btn-active ui-state-persist' data-theme='a'>關注度推波</a></li>";
				echo"<li><a href='Maps_App_Push_Single.php?id=".$devicename."&token=".$apptoken."' data-transition='none' data-theme='a'>個別推波</a></li>";
			?>
			</ul>
		</div>
	</div>
	<div data-role="main" class="ui-content">
		<form method="post" action="" id="formPush" name="formPush" data-ajax="false">
			<div class="push-slider">
				<label for="push-points">關注度選擇:</label>
				<input type="range" name="push-points" id="push-points" value="50" min="0" max="100" data-show-value="true" data-highlight="true">
			</div>
			<!--<div class="ui-field-contain">-->
			<div class="push-contain">
				<label for="push-text">推波內容</label>
				<input type="text" name="PushMessage" id="push-text" data-clear-btn="true" placeholder="請輸入內容.." required>       
				<label for="push-link">推波連結</label>
				<input type="url" name="PushLink" id="push-link" data-clear-btn="true" placeholder="http://.." required>
			</div>
			<div class="push-submit">
				<input name="action" type="hidden" value="push">
				<input type="submit" name="submit" id="submit" value="發送">
			</div>
		</form>
	</div>
	<div data-role="footer" data-position="fixed" data-theme="b">
      <!--
      <div data-role="navbar">
		<ul>
			<li><a href="Maps_App.php" data-icon="grid">App</a></li>
			<li><a href="Maps_About.php" data-icon="info">ABOUT</a></li>
		</ul>
	  </div>
	-->
    </div>
  </div>
  
  <!-- About頁面-->
  
</body>
</html>