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
    Variable: Frames
    All the frames from the game is loaded into this variable
    */
    private $Frames;

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
        if ($this->Frames[$frame][0] == 10) {
            return true;
        } else {
            return false;
        }
    }

    /*
    Function: isBonusStrike
    Returns true if first and second throw in frame is equal to 10.
    */

    public function isBonusStrike($frame)
    {
        if ($this->Frames[$frame][0] == 10 && $this->Frames[$frame][1] == 10) {
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
        if (($this->Frames[$frame][0] + $this->Frames[$frame][1]) == 10 && $this->Frames[$frame][0] != 10) {
            return true;
        } else {
            return false;
        }
    }


    /*
    Function: nextTwoPoints
    Returns the summation value of the frames first and second throw in frame.
    */
    public function nextTwoPoints($frame)
    {
        if (!$this->isBonusStrike($frame)) {
            return ($this->Frames[$frame][0] + $this->Frames[$frame][1]);
        } else {
            return $this->Frames[$frame][0];
        }
    }

    /*
    Function: nextPoint
    Returns specified value of certain frame.
    */
    public function nextPoint($frame, $position)
    {
        if (isset($this->Frames[$frame][$position])) {
            return ($this->Frames[$frame][$position]);
        } else {
            return false;
        }
    }

	/*
    Function: checkPoint
    Check if value is set
    */
    public function checkPoint($frame, $position)
    {
        if (isset($this->Frames[$frame][$position])) {
            return true;
        } else {
            return false;
        }
    }

	/*
    Function: setFrame
    Set private value Frames to received data
    */
    public function setFrame($data)
    {
        $this->Frames = $data;
    }

    /*
    Function: getFromAPI
    Returns decoded JSON data from SKAT Bowling API
    */
    public function getFromAPI()
    {
        $json = file_get_contents('http://37.139.2.74/api/points');
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
        if (!is_object($data)) {
            throw new \Exception('Input is not an object');
            return;
        }

        if (!property_exists($data, 'token') || empty($data->token)) {
            throw new \Exception('Token is empty or nonexisting');
            return;
        } else if (!property_exists($data, 'points') || empty($data->points)) {
            throw new \Exception('Points is empty or nonexisting');
            return;
        }

        $token  = $data->token;
        $frames = $data->points;
        $this->setFrame($data->points);

        $framearray = array();
        $framecount = 1;
        $rowcount   = 0;
        $totalscore = 0;
        for ($i = 0; $i < count($this->Frames); $i++) {
            $subscore = $totalscore;
            if ($this->SanitizeInteger($this->nextPoint($rowcount, 0), 0, 10) && $this->SanitizeInteger($this->nextPoint($rowcount, 1), 0, 10)) {
                if ($framecount <= 10) {
                    $framearray[$framecount][0] = $this->nextPoint($rowcount, 0) == 10 ? "X" : $this->nextPoint($rowcount, 0);
                    $framearray[$framecount][1] = $this->isSpare($rowcount) ? "/" : $this->nextPoint($rowcount, 1);
                    $framearray[$framecount][1] = $this->nextPoint($rowcount, 0) != 10 ? $framearray[$framecount][1] : "";
                    if ($this->isBonusStrike($rowcount) && $rowcount == 9) {
                        $subscore += 20;
                    } else if ($this->isStrike($rowcount)) {
                        if ($this->checkPoint($rowcount + 1, 0)) {
                            if ($this->checkPoint($rowcount + 1, 0) && $this->isStrike($rowcount + 1)) {
                                if ($this->checkPoint($rowcount + 2, 0)) {
                                    $subscore += 20 + $this->nextPoint($rowcount + 2, 0);
                                } else if ($this->nextPoint($rowcount + 1, 1) && $this->nextPoint($rowcount + 1, 1) == 10) {
                                    $subscore += 20 + $this->nextPoint($rowcount + 1, 1);
                                } else {
                                    $totalscore = $subscore;
                                    $subscore   = $this->LivePlay == true ? "" : $subscore += 20;
                                }
                            } else {
                                $subscore += 10 + $this->nextTwoPoints($rowcount + 1);
                            }
                        } else {
                            $subscore = $this->LivePlay == true ? "" : $subscore += 10;
                        }
                    } else if ($this->isSpare($rowcount)) {
                        $totalscore = $subscore;
                        $subscore   = $this->checkPoint($rowcount + 1, 0) ? ($subscore += 10 + $this->nextPoint($rowcount + 1, 0)) : ($this->LivePlay == true ? "" : $subscore += 10);
                    } else {
                        $subscore += $this->nextTwoPoints($rowcount);
                    }
                    if ($subscore != "") {
                        $totalscore = $subscore;
                    }
                    $framearray[$framecount]['score'] = $subscore;
                    $rowcount++;
                    $framecount++;
                } else {
                    if ($this->isBonusStrike($rowcount)) {
                        $framearray[10][1] = $this->nextPoint($rowcount, 0) == 10 ? "X" : $this->nextPoint($rowcount, 0);
                        $framearray[10][2] = $this->nextPoint($rowcount, 1) == 10 ? "X" : $this->nextPoint($rowcount, 1);
                    } else if ($this->isStrike($rowcount)) {
                        $framearray[10][1] = "X";
                    } else {
                        if ($this->isStrike($rowcount - 1)) {
                            $framearray[10][1] = $this->nextPoint($rowcount, 0) == 10 ? "X" : $this->nextPoint($rowcount, 0);
                            $framearray[10][2] = $this->nextPoint($rowcount, 1) == 10 ? "X" : $this->nextPoint($rowcount, 1);
                        } else {
                            $framearray[10][2] = $this->nextPoint($rowcount, 0) == 10 ? "X" : $this->nextPoint($rowcount, 0);
                        }
                    }
                }
            } else {
                throw new \Exception('Not inside allowed range');
                return;
            }
        }

        foreach ($framearray as $frame) {
            $points[] = $frame['score'];
        }
        $framearray['points'] = $points;
        $framearray['score']  = $totalscore;
        $framearray['token']  = $token;
        $framearray['mode']   = $this->LivePlay;
        return $framearray;
    }

    /*
    Function: validatePoints
    Checks if the calculated data is correct up against SKAT Bowling API
    Adds value Validated to array.
    The SKAT Bowling API is not designed for live play mode, so it fails if for example a spare or strike is the last value.
    */
    public function validatePoints($framearray)
    {
        $json_data = array(
            'token' => $framearray['token'],
            'points' => $framearray['points']
        );

        $json    = json_encode($json_data, true);
        $req_url = "http://37.139.2.74/api/points";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $req_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, count($json));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($curl);
        curl_close($curl);
        $result                  = json_decode($result);
        $framearray['validated'] = $result->success == 1 ? "true" : "false";

        if ($result->success != 1) {
            Log::notice("Calculation doesn't match API? Is that right? - " . json_encode($framearray)); // We only notice, because the verify API is not designed to Live Play mode.
        }

        return $framearray;
    }
}
