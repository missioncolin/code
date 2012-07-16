<?php

/***********************************
* haversine(lat1, lon1, lat2, lon2)
*
* returns the distance between two points
* on a map in km
***********************************/

function haversine ($l1, $o1, $l2, $o2)
{
    $l1    = deg2rad ($l1);
    $sinl1 = sin ($l1);
    $l2    = deg2rad ($l2);
    $o1    = deg2rad ($o1);
    $o2    = deg2rad ($o2);
                
    return (7926 - 26 * $sinl1) * asin (min (1, 0.707106781186548 * sqrt ((1 - (sin ($l2) * $sinl1) - cos ($l1) * cos ($l2) * cos ($o2 - $o1)))));
}



/***********************************
* cmp(string, string)
*
* callback function for usort from the
* dealer locator.
***********************************/

function cmp($a, $b)
{
	if ($a["distance"] < $b["distance"]) return -1;
	if ($a["distance"] > $b["distance"]) return 1;
	return 0;
}


?>