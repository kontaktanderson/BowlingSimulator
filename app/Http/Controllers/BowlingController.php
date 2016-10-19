<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Requests;
use App\Bowling;

class BowlingController extends Controller
{
	/*
	Variable: LivePlay
	If set true, the game calculates like the game is currently in play - meaning it doesn't calculate spares and strikes until their bonus point is available.
	If set false, the game calculates like the game time has ended - calculating last strikes and spares as 10 without bonus point.
	*/
	private $LivePlay;

	/*
		Function: index
		Set play mode and returns content of function runBowlingCalculator

		Live play mode: $this->LivePlay = true;
		Time ended mode: $this->LivePlay = false;
	*/
    public function index()
    {
      $this->LivePlay = true;
			return $this->runBowlingCalculator();
    }
	/*
		Function: show
		Set time ended mode and returns content of function runBowlingCalculator

		Live play mode: $this->LivePlay = true;
		Time ended mode: $this->LivePlay = false;
	*/
	  public function show()
	  {
	    $this->LivePlay = false;
			return $this->runBowlingCalculator();
		}

	/*
		Function: runBowlingCalculator
		Calls Bowling Model and return collected data to view
	*/
		private function runBowlingCalculator()
		{
			$bowling = new Bowling($this->LivePlay);
		  $jsondata = $bowling->getFromAPI();
		  try{
				$points = $bowling->getPoints($jsondata);
		  } catch (\Exception $e) {
				Log::alert($e->getMessage());
		  }
		  $points = $bowling->validatePoints($points);
      return view('bowling', array('scores' => $points));
		}
}
