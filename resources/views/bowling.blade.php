<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Bowling assignment</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            table.scorecard {margin: 0 auto; width:80%; font-size:12px; border:1px solid; text-align: center; table-layout: fixed; margin-bottom: 40px;}
            table.scorecard th, tr, td {padding: 0; vertical-align: middle; font-family: Arial, Helvetica, sans-serif; text-align: center;}
            table.scorecard th {border-bottom:1px solid; background-color:#d3313a; height:30px; color: #fff}
            table.scorecard th:not(:last-child) {border-right:1px solid;}
            table.scorecard td {height:30px; background: rgba(255, 255, 255, 0.5);}
            table.scorecard tr td:not(:last-child) {border-right:1px solid;}
            table.scorecard tr:nth-child(2) td:nth-child(even) {border-bottom:1px solid;}
            table.scorecard tr:nth-child(2) td:last-child {border-bottom:1px solid;}

            .title {
                font-size: 84px;
            }

            .links {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    Bowling Assignment
                </div>
                <table id='scorecardTable' class='scorecard' cellpadding='1' cellspacing='0'>
    <tr>
      <th colspan='6'>Frame 1</th>
      <th colspan='6'>Frame 2</th>
      <th colspan='6'>Frame 3</th>
      <th colspan='6'>Frame 4</th>
      <th colspan='6'>Frame 5</th>
      <th colspan='6'>Frame 6</th>
      <th colspan='6'>Frame 7</th>
      <th colspan='6'>Frame 8</th>
      <th colspan='6'>Frame 9</th>
      <th colspan='6'>Frame 10</th>
      <th colspan='6'>Score</th>
    </tr>
    <tr>
      @for ($i = 1; $i <= 9; $i++)
      <td colspan='3'>
        @if (isset($scores[$i][0]))
         {{ $scores[$i][0] }}
         @endif
       </td><td id="frame{{ $i }}"colspan='3'>
        @if (isset($scores[$i][1]))
          {{ $scores[$i][1] }}
        @endif
       </td>
      @endfor
      <td colspan='2'>
        @if (isset($scores[10][0]))
         {{ $scores[10][0] }}
         @endif
       </td><td id="frame{{ $i }}"colspan='2'>
        @if (isset($scores[10][1]))
          {{ $scores[10][1] }}
        @endif
       </td>
       <td id="frame{{ $i }}"colspan='2' style='border-bottom:1px solid !important'>
        @if (isset($scores[10][2]))
          {{ $scores[10][2] }}
        @endif
       </td>
       <td colspan="6" style="border:0px !important">
         {{ $scores['score'] }}
       </td>
    </tr>
    <tr>
      @for ($i = 1; $i <= 10; $i++)
      <td colspan='6'id="marker{{ $i }}">
        @if (isset($scores[$i]['score']))
          {{ $scores[$i]['score'] }}
        @endif
      </td>
      @endfor
      <td colspan="6">
      </td>
    </tr>
  </table>
  @if ($scores['mode'] == true)
	<h3>Live game mode</h3>
	<h4>When in live game mode,  the game calculates like the game is currently in play - meaning it doesn't calculate spares and strikes until their bonus point is available.</h4>
  <a href="/bowling/timeended" class="links">Click here to change to Time Ended Mode</a><br /><br />
  @else
	<h3>Time ended mode</h3>
	<h4>When in time ended mode, the game calculates like the game time has ended - calculating last strikes and spares as 10 without bonus point.</h4>
  <a href="/bowling/" class="links">Click here to change to Live Play Mode</a><br /><br />
  @endif
  Validated from SKAT Bowling API: {{ $scores['validated'] }} <br />(API validation only trustworthy then using time ended mode - because API is designed for that)
  @if ($scores['validated'] == "test")
	<script>
	location.reload();
	</script>
  @endif
            </div>
        </div>
    </body>
</html>
