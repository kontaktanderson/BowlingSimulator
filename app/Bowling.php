<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Bowling extends Model
{
    /*
		Variable: LivePlay
		If set true, the game calculates like the game is currently in play - meaning it doesn't calculate spares and strikes until their bonus point is available.
		If set false, the game calculates like the game time has ended - calculating last strikes and spares as 10 without bonus point.
	*/
	private $LivePlay;
	
	/*
		Function: Construct
		If LivePlay is set true, the game calculates like the game is currently in play - meaning it doesn't calculate spares and strikes until their bonus point is available.
		If LivePlay is set false, the game calculates like the game time has ended - calculating last strikes and spares as 10 without bonus point.
	*/
	function __construct($LivePlay = true)
	{
		$this->LivePlay = $LivePlay;
	}
	
	/*
		Function: SanitizeInteger
		Returns true if given value is an integer and within minimum and maximum values
	*/
	
	public function SanitizeInteger($value, $min, $max)
    {
        return filter_var($value, FILTER_VALIDATE_INT) === false ? false : $value >= $min && $value <= $max ? true : false;
    }

	/*
		Function: isStrike
		Returns true if first throw in frame is equal to 10.
	*/
	
    public function isStrike($frame)
    {
        if ($frame[0] == 10) {
            return true;
        } else {
            return false;
        }
    }

	/*
		Function: isSpare
		Returns true if first and second throw in frame is equal 10 and isn't a strike. 
	*/
	
    public function isSpare($frame)
    {
        if (($frame[0] + $frame[1]) == 10 && $frame[0] != 10) {
            return true;
        } else {
            return false;
        }
    }

	
	/*
		Function: nextTwoPoints
		Returns the summation value of the frames first and second throw in frame.
		This is used at Strikes
	*/
    public function nextTwoPoints($frame)
    {
        return ($frame[0] + $frame[1]);
    }
	
	/*
		Function: nextPoint
		Returns first value of frame. 
		This is used at Spare.
	*/
    public function nextPoint($frame)
    {
        return ($frame[0]);
    }
	
	/*
		Function: getFromAPI
		Returns decoded JSON data from SKAT Bowling API
	*/
	public function getFromAPI()
	{
		$json     = file_get_contents('http://37.139.2.74/api/points');
        $jsondata = json_decode($json);
		
		return $jsondata;
	}
	
	/*
		Function: getPoints
		Calculate the point score for each frame and total score. 
		Function contains checks for faulty input.
		Returns an array with the score data and token for validation.
	*/

    public function getPoints($data)
    {
        if(!is_object($data)){
			throw new \Exception('Input is not an object');
			return;
		}
		
		if(!property_exists($data, 'token') || empty($data->token)){
			throw new \Exception('Token is empty or nonexisting');
			return;
		}else if(!property_exists($data, 'points') || empty($data->points)){
			throw new \Exception('Points is empty or nonexisting');
			return;
		}
		
		$token      = $data->token;
		$frames     = $data->points;

        $score      = 0;
        $framearray = array();
        $framecount = 1;
        $rowcount = 0;
		$totalscore = 0;
        for ($i = 0; $i < count($frames); $i++) {
		  if($this->SanitizeInteger($frames[$rowcount][0], 0, 10) && $this->SanitizeInteger($frames[$rowcount][1], 0, 10)){
			  if($framecount <= 10){
				$framearray[$framecount][0] = $frames[$rowcount][0] == 10 ? "X" : $frames[$rowcount][0];
				$framearray[$framecount][1] = $frames[$rowcount][0]+$frames[$rowcount][1] != 10 ? $frames[$rowcount][1] : "/";
				$framearray[$framecount][1] = $frames[$rowcount][0] != 10 ? $framearray[$framecount][1] : "";

				if ($this->isStrike($frames[$rowcount]) && isset($frames[$rowcount+1])) {
					$nextpoint = $this->nextTwoPoints($frames[$rowcount+1]);
					$score += 10 + $nextpoint;
					$totalscore = $score;
					if ($this->isStrike($frames[$rowcount+1]) && isset($frames[$rowcount+2])) {
						$nextpoint = $this->nextTwoPoints($frames[$rowcount+2]);
						$score += $nextpoint;
						$totalscore = $score;
					}else if ($this->isStrike($frames[$rowcount+1]) && !isset($frames[$rowcount+2])) {
						$totalscore = $score - 20;
						$score = "";
					}
				} else if ($this->isStrike($frames[$rowcount]) && !isset($frames[$rowcount+1]) && $this->LivePlay == true) {
					$score = "";
				} else if ($this->isSpare($frames[$rowcount]) && isset($frames[$rowcount+1])) {
					$nextpoint = $this->nextPoint($frames[$rowcount+1]);
					$score += 10 + $nextpoint;
					$totalscore = $score;
				} else if ($this->isSpare($frames[$rowcount]) && !isset($frames[$rowcount+1]) && $this->LivePlay == true) {
					$score = "";
				} else {
					$score += $frames[$rowcount][0];
					$score += $frames[$rowcount][1];
					$totalscore = $score;
				}
				if($framecount == 11){
				  $framecount--;
				}
				$framearray[$framecount]['score'] = $score;
				$framecount++;
				$rowcount++;
			  }else{
				if($frames[$rowcount][0] == 10){
				  $framearray[10][1] = $frames[$rowcount][0] == 10 ? "X" : $frames[$rowcount][0];
				}else{
				  $framearray[10][2] = $frames[$rowcount][0] == 10 ? "X" : $frames[$rowcount][0];
				}
				$framearray[10][3] = $frames[$rowcount][0] != 10 ? $frames[$rowcount][1] : "";
			  }
		  }else{
			throw new \Exception('Not inside allowed range');
			return;
		  }
        }
		foreach($framearray as $frame){
          $points[] = $frame['score'];
        }
		$framearray['points'] = $points;
        $framearray['score'] = $totalscore;
		$framearray['token'] = $token;
		$framearray['mode'] = $this->LivePlay;
        return $framearray;
    }

	/*
		Function: validatePoints
		Checks if the calculated data is correct up against SKAT Bowling API
		Adds value Validated to array.
		The SKAT Bowling API is often incorrect, if for example a spare or strike is the last value.
	*/
    public function validatePoints($framearray)
    {
        $json_data = array(
        'token' => $framearray['token'],
        'points' => $framearray['points']
        );

        $json = json_encode($json_data, true);
        $req_url = "http://37.139.2.74/api/points";

        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $req_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, count($json));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result);
        $framearray['validated'] = $result->success == 1 ? "true" : "false";
		
		if($result->success != 1){
			Log::notice("Calculation doesn't match API? Is that right? - ".json_encode($framearray)); // We only notice, because the verify API is often incorrect.
		}
		
		return $framearray;
    }
}
