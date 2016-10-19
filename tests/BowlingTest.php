<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Bowling;

class BowlingTest extends TestCase
{
    /*
		A test script to test all functionalities of BowlingClass
		Christian Anderson
		17-10-2016
    */
    public function testExample()
    {
		$bowling = new Bowling(true);
		/*
			Check accessibility for page
		*/
		$this->visit('/')->see('Bowling assignment');

    /*
			Check router and mode select
		*/
    $this->visit('bowling/')->see('<h3>Live game mode</h3>');

    /*
			Check router and mode select
		*/
    $this->visit('bowling/timeended')->see('<h3>Time ended mode</h3>');

		/*
			Check sanitize function
		*/
		$this->assertTrue($bowling->SanitizeInteger(0, 0, 10));
		$this->assertTrue($bowling->SanitizeInteger(10, 0, 10));
		$this->assertFalse($bowling->SanitizeInteger(11, 0, 10));
		$this->assertFalse($bowling->SanitizeInteger(-1, 0, 10));

		/*
			Check if isStrike function works
		*/
		$frame = array(array('10','0'));
		$bowling->setFrame($frame);
		$this->assertTrue($bowling->isStrike(0));
		$frame = array(array('5','5'));
		$bowling->setFrame($frame);
		$this->assertFalse($bowling->isStrike(0));
		$frame = array(array('0','10'));
		$bowling->setFrame($frame);
		$this->assertFalse($bowling->isStrike(0));

		/*
			Check if isBonusStrike function works
		*/
		$frame = array(array('10','10'));
		$bowling->setFrame($frame);
		$this->assertTrue($bowling->isBonusStrike(0));
		$frame = array(array('5','5'));
		$bowling->setFrame($frame);
		$this->assertFalse($bowling->isBonusStrike(0));
		$frame = array(array('0','10'));
		$bowling->setFrame($frame);
		$this->assertFalse($bowling->isBonusStrike(0));

		/*
			Check if isSpare function works
		*/
		$frame = array(array('5','5'));
		$bowling->setFrame($frame);
		$this->assertTrue($bowling->isSpare(0));
		$frame = array(array('10','0'));
		$bowling->setFrame($frame);
		$this->assertFalse($bowling->isSpare(0));
		$frame = array(array('0','10'));
		$bowling->setFrame($frame);
		$this->assertTrue($bowling->isSpare(0));

		/*
			Check if nextTwoPoints works
		*/
		$frame = array(array('3','5'));
		$bowling->setFrame($frame);
		$this->assertTrue($bowling->nextTwoPoints(0) == 8);

		/*
			Check if nextPoint works
		*/
		$frame = array(array('7','2'));
		$bowling->setFrame($frame);
		$this->assertTrue($bowling->nextPoint(0,0) == 7);
		$this->assertTrue($bowling->nextPoint(0,1) == 2);

		/*
			Check if getFromAPI returns value
		*/
		$jsondata = $bowling->getFromAPI();
		$this->assertTrue(is_array($jsondata->points));
		$this->assertFalse(empty($jsondata->token));

		/*
			Check if getPoints returns values
		*/
		$points = $bowling->getPoints($jsondata);
		$this->assertFalse(empty($points['token']));
		$this->assertTrue(is_array($points['points']));
		$this->assertFalse(empty($points['score']));
		$this->assertTrue(is_array($points[1]));
		$this->assertTrue(isset($points[1][0]));

		/*
			Test check for object
		*/
		$check = false;
		try {
			$points = $bowling->getPoints(false);
		} catch (\Exception $e) {
			if($e->getMessage() == "Input is not an object")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for non existing token
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array(array(10,0));
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Token is empty or nonexisting")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for empty token
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array(array(10,0));
			$testdata->token = "";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Token is empty or nonexisting")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for non existing points
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->token = "not-needed";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Points is empty or nonexisting")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for empty points
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array();
			$testdata->token = "not-needed";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Points is empty or nonexisting")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for points overload value 1
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array(array(11,0));
			$testdata->token = "not-needed";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Not inside allowed range")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for points overload value 2
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array(array(10,11));
			$testdata->token = "not-needed";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Not inside allowed range")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for points underload value 1
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array(array(-1,1));
			$testdata->token = "not-needed";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Not inside allowed range")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Test check for points underload value 2
		*/
		$check = false;
		try {
			$testdata = new stdClass;
			$testdata->points = array(array(1,-1));
			$testdata->token = "not-needed";
			$points = $bowling->getPoints($testdata);
		} catch (\Exception $e) {
			if($e->getMessage() == "Not inside allowed range")
				$check = true;
		}
		$this->assertTrue($check);

		/*
			Create test data to verify calculation - full strikes
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 300);
		$checkarray = array(30, 60, 90, 120, 150, 180, 210, 240, 270, 300);
		$this->assertTrue($points['points'] === $checkarray);

    /*
			Create test data to verify calculation - 11 strikes
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0),array(10,0));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 270);
		$checkarray = array(30, 60, 90, 120, 150, 180, 210, 240, 270, "");
		$this->assertTrue($points['points'] === $checkarray);

		/*
			Create test data to verify calculation - full random spares
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(1,9),array(2,8),array(3,7),array(4,6),array(5,5),array(6,4),array(7,3),array(8,2),array(9,1),array(8,2), array(7, 0));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 159);
		$checkarray = array(12, 25, 39, 54, 70, 87, 105, 124, 142, 159);
		$this->assertTrue($points['points'] === $checkarray);

		/*
			Create test data to verify calculation - full 5/5 spares
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(5,5),array(5,5),array(5,5),array(5,5),array(5,5),array(5,5),array(5,5),array(5,5),array(5,5),array(5,5), array(5, 0));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 150);
		$checkarray = array(15, 30, 45, 60, 75, 90, 105, 120, 135, 150);
		$this->assertTrue($points['points'] === $checkarray);

		/*
			Create test data to verify calculation - full 1/1
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(1,1),array(1,1),array(1,1),array(1,1),array(1,1),array(1,1),array(1,1),array(1,1),array(1,1),array(1,1));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 20);
		$checkarray = array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20);
		$this->assertTrue($points['points'] === $checkarray);

		/*
			Create test data to verify calculation - full Gutterball
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0),array(0,0));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 0);
		$checkarray = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		$this->assertTrue($points['points'] === $checkarray);

		/*
			Create test data to verify calculation - two strikes - no calculation yet
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(10,0),array(10,0));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 0);
		$checkarray = array("", "");
		$this->assertTrue($points['points'] === $checkarray);

		/*
			Create test data to verify calculation - two spares - no calculation for last
		*/
		$testdata = new stdClass;
		$testdata->points = array(array(5,5),array(5,5));
		$testdata->token = "not-needed";
		$points = $bowling->getPoints($testdata);
		$this->assertTrue($points['score'] == 15);
		$checkarray = array(15, "");
		$this->assertTrue($points['points'] === $checkarray);

    }
}
