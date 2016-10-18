BowlingAssignment
=============

This assignment is a bowling score calculator.
It calculated the score recieved from an API and can calculate in "time ended mode" and "live play mode".
In Live Play mode, the game calculates like the game is currently in play - meaning it doesn't calculate spares and strikes until their bonus point is available.
In Time ended mode, the game calculates like the game time has ended - calculating last strikes and spares as 10 without bonus point.

The application is default to Live Play mode - and the provided SKAT Bowling validation API runs in Time eneded mode. To see it fully working, you need to change mode to "Time ended mode".
Instructions are in "Usage"

Custom files
-----------
0. app/Bowling.php (Model - Calculation functions)
0. app/Http/Controllers/BowlingController.php (Controller)
0. resources/views/Bowling.blade.php (View)
0. routes/web.php (Routing);
0. tests/BowlingTest.php (PHPUnit testfile)

Installation
-----------

You can either choose to copy the entire folder inside a LAMP server and access the public folder or install a dockerhub server.
The docker is located at chaj/bowlingassignment

```
docker run -p 80:80 -d chaj/bowlingassignment
```
The function routes host port 80 to docker port 80.

Usage
-----
Access homepage and see the program in action.
To change the calculation from Live play mode to Time ended mode, you must change the app/Http/Controllers/BowlingController.php, instructions is in the file.

Testing
-----
You can run a sequence test to verify the application by running:
```
cd /var/www
php vendor/phpunit/phpunit/phpunit tests
```
The test is started and should test all functions.
