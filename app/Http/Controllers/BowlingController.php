<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Requests;
use App\Bowling;

class BowlingController extends Controller
{
	/*
		Function: index
		Calls Bowling Model and return collected data to view
		
		Live play mode: $bowling = new Bowling(true);
		Time ended mode: $bowling = new Bowling(false);
	*/
    public function index()
    {
      $bowling = new Bowling(true);
	  $jsondata = $bowling->getFromAPI();
	  try{
		$points = $bowling->getPoints($jsondata);
	  } catch (\Exception $e) {
		Log::alert($e->getMessage());
	  }
	  $score = $bowling->validatePoints($points);
      return view('bowling', array('scores' => $score));
    }
}
