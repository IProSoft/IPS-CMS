<?php

/* 
 *************** Google Analytics Php Class ***********************
 *************** @author :- Aman Virk ***************************
 **************  @uri :- www.thetutlage.com ********************
 ************** @browse all api and php class :- 
 http://www.thetutlage.com/index.html?parent=Tutorials&&child=Api&Classes
*/




class fetchAnalytics extends gapi
{
		public $pageViews;
		public $visits;
		public $initialstartdate;
		public $initialenddate;
		public $bounceRate;
		public $timeOnSite;
		public $newVisits;
		public $source;
		public $geo;
		public $valueCountsPerDay;
		
		// construct to hit and get values from google
		function __construct( $email, $password, $uid, $start_date=null, $end_date=null )
		{
			

			$this->initialstartdate = date( 'M j Y', strtotime($start_date) );
			$this->initialenddate = date( 'M j Y', strtotime($end_date) );

			$perdayCounts = $this->createDateRangeArray( $start_date, $end_date );

			parent::__construct( $email, $password);
			
			/* 
			$this->requestReportData($uid, array('date','source','medium','referralPath', 'socialInteractionNetwork'),array('pageviews','visits','entranceBounceRate','timeOnSite','newVisits', 'UniquePageviews', 'socialInteractions'),'date','',$start_date,$end_date, 1, 1000000000);
			 */
			
			$this->requestReportData( $uid, array('date','source','medium','referralPath', 'visitLength'),array('pageviews','visits','entranceBounceRate','timeOnSite','newVisits', 'UniquePageviews', 'avgTimeOnSite'),'date', '' ,$start_date,$end_date, 1, 1000000000 );
			
			$result = $this->getResults();
			//print_r($this->getavgTimeOnSite());
			
			$this->pageViews = $this->getPageviews();
			$this->visits = $this->getVisits();
			$this->visits = $this->visits == 0 ? 1 : $this->visits ;
			$this->uniquePageviews = $this->getUniquePageviews();
			$this->bounceRate = $this->getentranceBounceRate();
			$this->averageTimeOnSite = $this->getavgTimeOnSite() / 60;
			//$this->timeOnSite = $this->gettimeOnSite() / $this->visits / 60;
			$this->newVisits = round(($this->getnewVisits() / $this->visits) * 100, 2) ;
			
			foreach($result as $key => $value)
			{
				$this->source[] = array('medium' => $value->getMedium(), 'visit' => $value->getVisits(), 'source' => $value->getSource());
				$this->dateWise[] = array($value->getDate() => $value->getPageviews());
			}
			foreach($perdayCounts as $day)
			{
				$new_array = array();
				
				if( is_array( $this->dateWise ) )
				{
					foreach( $this->dateWise as $breakPoint )
					{
						foreach($breakPoint as $key => $value)
						{
							if($key == $day)
							{
								$new_array[] = $value;
							}
						}
					}
					$this->valueCountsPerDay[] = array($day => array_sum($new_array));
				}
			}
		}

		function createDateRangeArray($strDateFrom,$strDateTo)
		{
			$aryRange=array();
			$iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),substr($strDateFrom,8,2),substr($strDateFrom,0,4));
			$iDateTo=mktime(1,0,0,substr($strDateTo,5,2),substr($strDateTo,8,2),substr($strDateTo,0,4));
			if ($iDateTo>=$iDateFrom)
			{
				array_push($aryRange,date('Ymd',$iDateFrom)); // first entry
				while ($iDateFrom<$iDateTo)
				{
					$iDateFrom+=86400; // add 24 hours
					array_push($aryRange,date('Ymd',$iDateFrom));
				}
			}
			return $aryRange;
		}

		function trafficCount(){

			return array('pageView' => $this->pageViews,'visits' => $this->visits,'bounceRate' => $this->bounceRate,'timeOnSite' => $this->averageTimeOnSite,'newVisits' => $this->newVisits, 'unique' => $this->uniquePageviews );
		}
		
		function referralCount(){
			$array = array();
			if( !is_array( $this->source ) ) $this->source = array();
			foreach( $this->source as $sources )
			{
				$key = $sources['source'];
				$allSources[$key] = $sources['source'];
			}
		
			foreach($this->source as $me)
			{
				$key = $me['source'];
				if( !isset($array[$key]) )
				{
					$array[$key] = 0;
				}
				if( $me['source'] == $allSources[$key] )
				{
					$array[$key] += $me['visit'];
				}
				else
				{
					$array[$key] = $me['visit'];
				}
			}
			return $array;
		}
		function sourceCountNum(){
			foreach($this->source as $medium){
				$key = $medium['medium'];
				$allSources[$key] = $medium['medium'];
			}
			$array = array();
			foreach($this->source as $me)
			{
				$key = $me['medium'];
				if( !isset($array[$key]) )
				{
					$array[$key] = 0;
				}
				if($me['medium'] == $allSources[$key])
				{
					if($key == '(brak)')
					{ 
						$key = 'bezpośrednie'; 
					}
					if( !isset($array[$key]) )
					{
						$array[$key] = 0;
					}
					$array[$key] += $me['visit'];
				}
				else
				{
					if( $key == '(brak)' )
					{
						$key = 'bezpośrednie';
					}
					if( !isset($array[$key]) )
					{
						$array[$key] = 0;
					}
					$array[$key] = $me['visit'];
				}
			}
			return $array;
		}
		
		function sourceCountPer(){
			$array = array();
			foreach($this->source as $medium){
				$key = $medium['medium'];
				$allSources[$key] = $medium['medium'];
			}
			foreach($this->source as $me)
			{
				$key = $me['medium'];
				if( !isset($array[$key]) )
				{
					$array[$key] = 0;
				}
				if($me['medium'] == $allSources[$key])
				{
					if( $key == '(brak)' ) 
					{
						$key = 'bezpośrednie'; 
					}
					if( !isset($array[$key]) )
					{
						$array[$key] = 0;
					}
					$array[$key] += $me['visit'];
				}
				else
				{
					if($key == '(brak)')
					{
						$key = 'bezpośrednie';
					}
					if( !isset($array[$key]) )
					{
						$array[$key] = 0;
					}
					$array[$key] = $me['visit'];
				}
			}
			$total_traffic = array_sum($array);
			foreach($array as $key => $value){
				$array_value[$key] = round(($value * 100) / $total_traffic, 2);
			}
			return $array_value;
		}
		function perDayCount(){
			$initialPerDayCount = $this->valueCountsPerDay;
			foreach($initialPerDayCount as $breakPoint)
			{
				foreach($breakPoint as $key => $value)
				{
					$year_split = str_split($key,4);
					$formatDate = date('M j',strtotime($key));
					//$construct_new_date = $year . '-' . $month . '-' . $day;
					$result[$formatDate] = $value;
				}
			}
			return $result;
		}
		function graphSourceCount($width = 400,$height = 275,$colors = array('ec561b','50b332','06C891','058dc7','058dc7','e6f4fa'),$chart_bg = 'C3D9FF',$chart_type = '2d'){
			$trafficCountPer = $this->sourceCountPer();
			if( !is_array( $trafficCountPer ) || empty( $trafficCountPer ) )
			{
				$trafficCountPer = array( '(none)' => 0, 'referral' => 0, 'organic' => 0 );
			}
			foreach($trafficCountPer as $key => $value) {
				$chart_d[] = $value;
				$chart_l[] = $key;
				$chart_l_d[] = $key.' ( '.$value.'% )';
			}
			$chart_data = implode(',',$chart_d);
			$chart_label_values = implode('|',$chart_l_d);

			if($chart_type == '3d') { $string = 3; } else { $string = ''; }
			if(is_array($colors))
			{
				$chart_colors = implode(',',$colors);
			}
			else
			{
				$chart_colors = 'ec561b,50b332,06C891,058dc7,058dc7,e6f4fa';
			}
			return '<img src="http://chart.apis.google.com/chart?chf=bg,s,'.$chart_bg.'&chxs=0,505050,11.5&chs=400x275&cht=p'.$string.'&chco='.$chart_colors.'&chd=t0:'.$chart_data.'&chdl='.$chart_label_values.'&chma=0,130" width="'.$width.'" height="'.$height.'" alt=Unable to generate graph" />';
		}

		function graphVisitorType($width = 400,$height = 275,$colors = array('ec561b','50b332'),$chart_bg = 'C3D9FF',$chart_type = '3d'){
			$newVisits = $this->newVisits;
			$returnVisits = 100 - $this->newVisits;
			$chart_data = $newVisits.','.$returnVisits;
			$chart_label_values = 'Nowe ('.$newVisits.'% )|Powracający ( '.$returnVisits.'% )';

			if($chart_type == '3d') { $string = 3; } else { $string = ''; }
			if(is_array($colors))
			{
				$chart_colors = implode(',',$colors);
			}
			else
			{
				$chart_colors = 'ec561b,50b332';
			}
			return '<img src="http://chart.apis.google.com/chart?chf=bg,s,'.$chart_bg.'&chxs=0,505050,11.5&chs=400x275&cht=p'.$string.'&chco='.$chart_colors.'&chd=t0:'.$chart_data.'&chdl='.$chart_label_values.'&chma=0,130" width="'.$width.'" height="'.$height.'" alt=Unable to generate graph" />';
		}

		function graphPerDayCount($divId = 'ga-graph',$color = '505050',$fillColor = '058dc7',$outlineColor = 'e6f4fa',$width= 640,$height = 320,$dateGap = 5){
			$perDayCount = $this->perDayCount();
		?>
			<div id="ga-graph"></div>
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript"> 
				google.load("visualization", "1", {packages:["corechart"]}); 
				google.setOnLoadCallback(drawChart);
				function drawChart() {
					var data = new google.visualization.DataTable();
					data.addColumn('string', 'Day');
					data.addColumn('number', 'Pageviews');
					data.addRows([
					<?php foreach($perDayCount as $key => $value) {
						echo '["'.$key.'", '.$value.'],';
					}
					?>
					]);
					 var chart = new google.visualization.AreaChart(document.getElementById('<?php echo $divId; ?>'));
					  chart.draw(data, {width: <?php echo $width; ?>, height: <?php echo $height; ?>, title: '<?php echo $this->initialstartdate; ?><?php echo ' - '.$this->initialenddate; ?>',
						colors:['#<?php echo $fillColor; ?>','#<?php echo $outlineColor; ?>'],
						areaOpacity: 0.1,
						hAxis: {textPosition: 'in', showTextEvery: <?php echo $dateGap; ?>, slantedText: false, textStyle: { color: '#<?php echo $color; ?>', fontSize: 10}},
						pointSize: 5,
						legend: 'none',
						chartArea:{left:0,top:30,width:"100%",height:"100%"}
					});
				}
				</script>
				
<?php } } ?>