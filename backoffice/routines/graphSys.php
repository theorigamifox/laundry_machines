<?php
	class graphSys {
	
		var $xAxis;
		var $yAxis;
		var $xNumValues;
		var $xValues;
		var $defaultColors;
		var $xColors;
		var $barWidth;
		var $graphWidth;
		var $graphHeight;
		var $defaultSplits;
		var $ySplit;
		var $yExtra;
		var $maxYSplits;
		var $numYSplits;
		var $xTicks;
		var $graphTitle;
		var $showKey;
		var $theKey;
		
		function graphSys() {
			$this->xAxis = "";
			$this->yAxis = "";
			$this->xNumValues = 0;
			$this->defaultColors[] = array("Rainbow",array("Red","Green","Blue","Orange"));
			$this->defaultSplits = array(1,2,5,10,20,50,100,200,500,1000,2000,5000,10000,20000,50000,100000,200000,500000,1000000);
			$this->barWidth = 0; //if barWidth = 0 then we'll calculate it, otherwise used specified width
			$this->ySplit = 10;
			$this->numYSplits = 10;
			$this->yExtra = 8; // %extra to add to max value when calculating
			$this->maxYSplits = 10;
			$this->graphHeight = 200; //looks like 160 is the lower limit here
			$this->graphWidth = 600;
			$this->xTicks = "";
			$this->showKey = 0;
		}

		function setGraphTitle($new_name) {
			$this->graphTitle = $new_name;
		}
		
		function showKey($newvalue) {
			$this->showKey = $newvalue;
		}
		
		function setKey($keysArray) {
			$this->theKey = $keysArray;	
		}

		function setXAxis($new_name,$new_ticks) {
			$this->xAxis = $new_name;
			$this->xTicks = $new_ticks;
		}
		
		function setYAxis($new_name) {
			$this->yAxis = $new_name;
		}

		function setXEntries($new_total) {
			$this->xEntries = $new_total;
		}
		
		function addXValues($new_values,$new_colors) {
			$this->xValues[] = $new_values;
			if (is_array($new_colors)) {
				$this->xColors[] = $new_colors;
			} else {
				if ($new_colors == "Rainbow") {
					$this->xColors[] = array("red","orange","yellow","green","cyan","blue","purple");
				}
			}
			$this->xNumValues++;
		}
	
		function returnXColors($new_xvalue) {
			//got to zip round the xvalues, assign the colors and return an array
		}
		
		function getYSplit() {
			$cMax = 0;
			for ($f = 0; $f < count($this->xValues); $f++) {
				for ($g = 0; $g < count($this->xValues[$f]); $g++) {
					if ($this->xValues[$f][$g] > $cMax) {
						$cMax = $this->xValues[$f][$g];
					}
				}
			}
			$cMax = ceil((($this->yExtra/100)+1)*$cMax);
			
			$bestFit = 1;
			$bestDiff = 6000;
			$bestNum = 0;
			for ($f = 0; $f < count($this->defaultSplits); $f++) {
				$thisFit = ceil($cMax / $this->defaultSplits[$f]);
				if ($thisFit > $this->maxYSplits) {
					$thisDiff = $thisFit - $this->maxYSplits;
				} else {
					$thisDiff = $this->maxYSplits - $thisFit;
				}
				if ($thisDiff < $bestDiff) {
					$bestDiff = $thisDiff;
					$bestFit = $this->defaultSplits[$f];
					$bestNum = $thisFit;
				}

			}
			$this->ySplit = $bestFit;
			$this->numYSplits = $bestNum;
		}
		
		
		function drawGraph() {

			$totalX = count($this->xTicks);
			$graphSpaceX = $this->graphWidth - 63;
			$graphSpaceY = $this->graphHeight;
			$xWidth = ceil($graphSpaceX/$totalX);
			$yWidth = ceil($graphSpaceY/$this->numYSplits);

			$graphOutput = "";
			
			$graphOutput .= "<table cellpadding=0 cellspacing=0 border=0>";
			$graphOutput .= "<tr><td valign=top>";
			
			$graphOutput .= "<table cellpadding=0 cellspacing=0 border=0>";
			$graphOutput .= "<tr>";
			$graphOutput .= "<td valign=top align=left width=5><img src='images/graphs/line.gif' border=0 width=1 height=5><img src='images/graphs/line.gif' border=0 width=4 height=1 align=top></td>";
			$graphOutput .= "<td width=".($this->graphWidth+10)." valign=top><img src='images/graphs/line.gif' border=0 width=".($this->graphWidth+10)." height=1></td>";
			$graphOutput .= "<td valign=top align=left width=5><img src='images/graphs/line.gif' border=0 width=1 height=5></td>";
			$graphOutput .= "</tr>";
			$graphOutput .= "<tr>";
			$graphOutput .= "<td height=".($this->graphHeight+100)." align=left width=5 valign=top><img src='images/graphs/line.gif' border=0 width=1 height=".($this->graphHeight+100)."></td>";
			$graphOutput .= "<td valign=center align=center>";


			
			$graphOutput .= "<table cellpadding=0 cellspacing=0 border=0>";
			$graphOutput .= "<tr><td valign=top align=right><font face=Arial size=-1><b>".$this->graphTitle."</b></font></td></tr>";
			$graphOutput .= "<tr><td valign=top align=center>";
			
			$graphOutput .= "<table cellpadding=0 cellspacing=0 border=0 height=".$this->graphHeight." width=".$this->graphWidth.">";
			
			//Draw the Y axis
			for ($f = $this->numYSplits; $f >= 0; $f--) {
				if ($f == $this->numYSplits) {
					$addBlanker = "<td valign=top rowspan=".$this->numYSplits." colspan=$totalX width=$graphSpaceX height=$graphSpaceY>#graph#</td>";
					$addYTitle = "<td valign=center rowspan=".($this->numYSplits)." align=center><font face=Arial size=-2>";
					for ($g = 0; $g < strlen($this->yAxis); $g++) {
						$addYTitle =$addYTitle.substr($this->yAxis,$g,1)."<br>";
					}
					$addYTitle = $addYTitle."</font></td>";
				} else {
					$addBlanker = "";
					$addYTitle = "";
				}
				if ($f == 0) {
					$addFiller = " rowspan=4";
					$addYFiller = "<td rowspan=4></td>";
					$tWidth = 4;
				} else {
					$addFiller = "";
					$addYFiller = "";
					$tWidth=$yWidth;
				}
				$graphOutput .="<tr>$addYTitle$addYFiller<td align=right valign=top$addFiller height=$yWidth><font face=Arial size=-2>".$f*$this->ySplit."</font></td><td valign=top align=right width=3$addFiller><img src='images/graphs/line.gif' border=0 width=3 height=1></td><td valign=top align=right width=1$addFiller><img src='images/graphs/line.gif' border=0 width=1 height=$tWidth></td>$addBlanker";
				if ($f != 0) {
					$graphOutput .="</tr>";
				}
			}
			//Draw the X axis
			for ($f = 1; $f <= $totalX ; $f++) {
				$graphOutput .="<td width=$xWidth height=1><img src='images/graphs/line.gif' border=0 width=$xWidth height=1></td>";
			}
			$graphOutput .="</tr>";
			$graphOutput .="<tr>";
			for ($f = 1; $f <= $totalX ; $f++) {
				$graphOutput .="<td width=$xWidth height=3 align=right><img src='images/graphs/line.gif' border=0 width=1 height=3></td>";
			}		
			$graphOutput .="</tr>";	
			$graphOutput .="<tr>";
			for ($f = 1; $f <= $totalX ; $f++) {
				$graphOutput .="<td width=$xWidth height=".($yWidth-3)." valign=top align=center><font face=Arial size=-2>".$this->xTicks[$f-1]."</font></td>";
			}	
			$graphOutput .="</tr>";
			
			
			$graphOutput .="<tr><td colspan=$totalX valign=bottom align=center><font face=Arial size=-2>".$this->xAxis."</font></td></tr>";
			$graphOutput .="</table>";
			
			$graphOutput .="</td></tr></table>";
			
			

			$graphOutput .="</td>";
			$graphOutput .="<td height=".($this->graphHeight+100)." align=left width=5 valign=top><img src='images/graphs/line.gif' border=0 width=5 height=".($this->graphHeight+100)."></td>";
			$graphOutput .="</tr>";
			$graphOutput .="<tr>";
			$graphOutput .="<td valign=top width=5 height=5><img src='images/graphs/line.gif' border=0 width=5 height=1></td>";
			$graphOutput .="<td valign=top align=left width=".($this->graphWidth+10)." height=5><img src='images/graphs/line.gif' border=0 height=5 width=".($this->graphWidth+10)."></td>";
			$graphOutput .="<td valign=top align=left width=5 height=5><img src='images/graphs/line.gif' border=0 height=5 width=5></td>";
			$graphOutput .="</table>";
			
			$outputKey = "";
			if ($this->showKey == 1 && is_array($this->theKey))  {
				$outputKey .= "<p>";
				$outputKey .= "<table cellpadding=2 cellspacing=1 border=0>";
				$outputKey .= "<tr><td colspan='2' bgcolor=\"#dddddd\"><font face=Arial size=-2><b>Graph Key:</b></font></td></tr>";
				for ($f = 0; $f < count($this->theKey); $f++) {
					$outputKey .= "<tr><td valign='top'><font face=Arial size=-2>".$this->theKey[$f][0]."</font></td>";
					$outputKey .= "<td valign='top'><font face=Arial size=-2>".$this->theKey[$f][1]."</font></td></tr>";
				}
				$outputKey .= "</table>";
			}

			
			$graphOutput .= "</td><td>&nbsp;</td><td valign=top>$outputKey";
			
			$graphOutput .= "</td></tr></table>";

			
			$middleOutput = "<table width=".($totalX*$xWidth)." height=".($this->numYSplits*$yWidth)." cellpadding=0 cellspacing=0>";
			
			$middleOutput .="<tr>";
			
			$topTotal = ($this->numYSplits*$yWidth) / ($this->ySplit*$this->numYSplits);
			
			$thisColor = 0;
			
			$barWidth = floor($xWidth * .75);
			$barWidth = floor($barWidth/count($this->xValues));
			
			for ($f = 1; $f <= $totalX; $f++) {
				$middleOutput .="<td valign=bottom align=center>";
				
				for ($g = 0; $g < count($this->xValues); $g++) {
				
					$thisHeight = ($this->xValues[$g][$f-1])*$topTotal;
					$currentColor = 1;
					$currentColor = $this->findRemainder($f,count($this->xColors[$g]));
					$currentColor--;
				
					$middleOutput .="<img src='images/graphs/".$this->xColors[$g][$currentColor].".gif' border=0 alt=\"".$this->xValues[$g][$f-1]."\" width=$barWidth height=$thisHeight>";
				
					if ($g != count($this->xValues)-1) {
						$middleOutput .="<img src='images/graphs/filler.gif' border=0 width=2 height=10>";
					}
				}
				
				
				$middleOutput .="</td>";
			}
			$middleOutput .="</tr>";
			
			$middleOutput .= "</table>";
			$graphOutput = ereg_replace("#graph#",$middleOutput,$graphOutput);
			
			echo $graphOutput;
		}

		function findRemainder($the_value,$the_divider) {
			$myReturn =  $the_value - (floor($the_value/$the_divider)*$the_divider);
			if ($myReturn == 0) {
				$myReturn = $the_divider;
			}
			return $myReturn;
		}

	}
