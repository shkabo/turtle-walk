<?php
/*
 * Get new point based of old points, direction and size of the step
 * @deprecated
 */
function newPoint(array $oldPoint, int $direction, int $step) : array
{
    $x = $oldPoint[0];
    $y = $oldPoint[1];

    switch($direction) {
        case 1:
            // y going in positive direction
            return [$x, $y + $step];
            break;
        case 2:
            // x going in positive direction
            return [$x + $step, $y];
            break;
        case 3:
            // y going in negative direction
            return [$x, $y - $step];
            break;
        case 4:
            // x going in negative direction
            return [$x - $step, $y];
            break;

    }
}

/*
 * check if current line and previous lines of opposite orientation intersect
 * @deprecated
 */
function doIntersect(array $path, $orientation, array $points) : bool
{
    foreach( $path[$orientation] as $line ) {
        // set our points for two lines
        $p1 = [$line[0], $line[1]];
        $q1 = [$line[2], $line[3]];
        $p2 = [$points[0], $points[1]];
        $q2 = [$points[2], $points[3]];

        // set our x and y coords asc
        $px = [$p1[0], $q1[0]];
        sort($px);
        $py = [$p1[1], $q1[1]];
        sort($py);


        // check if smallest line2(x) is between line1(x1) and line1(x2)
        // or line2(y) is between line1(y1) and line1(y2)
        // if so, it's intersection
        if ($px[0] < $p2[0] && $p2[0] < $px[1] ||
            $py[0] < $p2[1] && $p2[1] < $py[1])
            return true;

    }
    return false;


}

/*
 * Check if intersection occured by checking coordinates
 * @deprecated
 */
function didCrossPreviousPathCoordinatesCehck(array $steps) : int
{
    // set default starting point coordinates
    // $x and $y are start point, $x1 and $y1 are end point
    $x1 = 0;
    $y1 = 0;

    // direction has 4 steps 90, 180, 270, 360 degrees, going clockwise
    $direction = 1;
    $stepsCount = 1;

    // save all the line coordinates and split them in two groups
    // since we don't want to check two horizontal lines or two vertical
    $lines = [];
    $lines['vertical'] = [];
    $lines['horizontal'] = [];

    foreach($steps as $step) {
        // set X and Y to previous values of X1 and Y1
        $x = $x1;
        $y = $y1;

        // get new point coordinate
        $newPoint = newPoint([$x, $y],  $direction, $step);
        $x1 = $newPoint[0];
        $y1 = $newPoint[1];

        // set the orientation opposite of the current line for checkup later
        // and save current line in the path array
        $lineOrientation = '';
        if ($x === $x1) {
            $lineOrientation = 'horizontal';
            array_push($lines['vertical'], [$x, $y, $x1, $y1]);
        } else {
            $lineOrientation = 'vertical';
            array_push($lines['horizontal'], [$x, $y, $x1, $y1]);
        }

        $intersect = doIntersect($lines, $lineOrientation, [$x, $y, $x1, $y1]);

        if ($intersect) return $stepsCount;

        $direction = ($direction !== 4) ? $direction + 1  : 1;
        $stepsCount++;
    }
    return 0;
}

/**
 * Check 4 numbers at a time to find possible intersection
 *
 * @param array $steps
 * @return int
 */
function didCrossPreviousPathPairsCheck(array $steps) : int
{
    $totalSteps = count($steps);
    $groups = (int) ceil($totalSteps / 4);
    if ($totalSteps % 4 !== 0)
    {
        $leftSteps = $totalSteps % 4;
    }
    // start point
    $y1 = 0;
    $x1 = 0;
    $y2 = 0;
    $x2 = 0;

    for($i = 1; $i <= $groups; $i++)
    {
        $stepPosition = ($i -1) * 4 ;

        // assign first 4 steps (full circle)
        if ($i === $groups && isset($leftSteps))
        {

            // if we have have steps that don't form full circle
            // we dynamically assign them for the later check
            $pointNames = ['yp1', 'xp1', 'yp2', 'xp2'];
            for($y = 0; $y < count($pointNames); $y++)
            {
                ${$pointNames[$y]} = array_key_exists($stepPosition + $y, $steps) ? $steps[$stepPosition + $y] : null;
            }
        } else {
            // we have full circle of points, so let's define them
            $yp1 = $steps[$stepPosition];
            $xp1 = $steps[$stepPosition + 1];
            $yp2 = $steps[$stepPosition + 2];
            $xp2 = $steps[$stepPosition + 3];
        }

        // check direction of circle - expanding or shrinking
        // first point of a new circle can tell us that
        if (isset($yp1)) {
            switch($yp1) {
                case $yp1 > max($y1, $y2):
                    //expanding
                    if (($res = pathPairCheckExpanding([$y1, $x1, $y2, $x2], $yp1 , $xp1 , $yp2 , $xp2)) > 0
                        && $stepPosition !== 0) {
                        return $stepPosition + $res;
                    };
                    break;
                case $yp1 < max($y1, $y2):
                    //shrinking
                    if (($res = pathPairCheckShrinking([$y1, $x1, $y2, $x2], $yp1 , $xp1 , $yp2 , $xp2)) > 0
                        && $stepPosition !== 0) {
                        return $stepPosition + $res;
                    }
                    break;
            }
        }

        // update starting points
        $y1 = $yp1;
        $x1 = $xp1;
        $y2 = $yp2;
        $x2 = $xp2;
    }
    // we didn't found crossing
    return 0;
}

/**
 * Check two sets of numbers for intersection/alignment if circle is expanding
 * @param array $pair1
 * @param mixed ...$pair2
 * @return int
 */
function pathPairCheckExpanding(array $pair1, ...$pair2): int
{
    // set initial points
    $y1 = $pair1[0];
    $x1 = $pair1[1];
    $y2 = $pair1[2];
    $x2 = $pair1[3];

    // set second set of points
    $pointNames = ['yp1', 'xp1', 'yp2', 'xp2'];
    for($y = 0; $y < count($pair2); $y++)
    {
        ${$pointNames[$y]} = $pair2[$y];
    }

    // validate and return result
    if     ($yp1 && ($exp = checkPairExpand($yp1, $y1, $y2, ($x1 == $x2))) > 0 ) return $exp + 0;
    elseif ($xp1 && ($exp = checkPairExpand($xp1, $x1, $x2, ($yp1 == $y2))) > 0 ) return $exp + 1;
    elseif ($yp2 && ($exp = checkPairExpand($yp2, $yp1, $y2, ($xp1 == $x2))) > 0 ) return $exp + 2;
    elseif ($xp2 && ($exp = checkPairExpand($xp2, $xp1, $x2, ($yp1 == $yp2))) > 0 ) return $exp + 3;
    else return 0;
}

/**
 * Check two sets of numbers for intersection/alignment if circle is shrinking
 * @param array $pair1
 * @param mixed ...$pair2
 * @return int
 */
function pathPairCheckShrinking(array $pair1, ...$pair2): int
{
    // set initial points
    $y1 = $pair1[0];
    $x1 = $pair1[1];
    $y2 = $pair1[2];
    $x2 = $pair1[3];

    // set second set of points
    $pointNames = ['yp1', 'xp1', 'yp2', 'xp2'];
    for($y = 0; $y < count($pair2); $y++)
    {
        ${$pointNames[$y]} = $pair2[$y];
    }

    // validate and return result
    if     ($yp1 && ($exp = checkPairShrink($yp1, $y1, $y2, ($x1 == $x2))) > 0 ) return $exp + 0;
    elseif ($xp1 && ($exp = checkPairShrink($xp1, $x1, $x2, ($yp1 == $y2))) > 0 ) return $exp + 1;
    elseif ($yp2 && ($exp = checkPairShrink($yp2, $yp1, $y2, ($xp1 == $x2))) > 0 ) return $exp + 2;
    elseif ($xp2 && ($exp = checkPairShrink($xp2, $xp1, $x2, ($yp1 == $yp2))) > 0 ) return $exp + 3;
    else return 0;
}

/**
 * Determine for each pair if there is possible intersection or alignment - Expanding
 * @param int $yp1
 * @param int $y1
 * @param int $y2
 * @param bool $matchingX
 * @return int
 */
function checkPairExpand(int $yp1, int $y1, int $y2, bool $matchingX): int
{
    if ($matchingX
        && $yp1 >= (max($y1, $y2) - min($y1, $y2))) {
        // alignment first line in the loop
        return 1;
    } elseif ($yp1 == max($y1, $y2)) {
         // alignment
        return 2;
    } elseif ($yp1 < max($y1, $y2)) {
         // crossing
        return 2;
    }else {
         // move on
        return 0;
    }
}

/**
 * Determine for each pair if there is possible intersection or alignment - Shrinking
 * @param int $yp1
 * @param int $y1
 * @param int $y2
 * @param bool $matchingX
 * @return int
 */
function checkPairShrink(int $yp1, int $y1, int $y2, bool $matchingX): int
{
    if ($matchingX
        && $yp1  >= (max($y1, $y2) - min($y1, $y2))
        && $yp1 <= max($y1, $y2)) {
        // alignment first line in the loop
        return 1;
    } elseif ($yp1 == max($y1, $y2)) {
        // alignment
        return 1;
    } elseif ($yp1 > max($y1, $y2)) {
        // crossing
        return 1;
    } else {
        // move on
        return 0;
    }
}

// shrink
$shrink0 = [
    'type' => 'Shrink normal',
    'arr' => [1,3,8,6,5,5],
    'expected' => 0
];
$shrink1 = [
    'type' => 'Shrink align',
    'arr' => [1,3,8,6,5,6,7,3],
    'expected' => 6
];
$shrink2 = [
    'type' => 'Shrink crossing',
    'arr' => [1,3,8,6,5,7,7,3],
    'expected' => 6
];

// expand
$expand0 = [
    'type' => 'Expand normal',
    'arr' => [1,3,8,6,9,7,10],
    'expected' => 0
];
$expand1 = [
    'type' => 'Expand align',
    'arr' => [1,3,8,6,9,7,9,3],
    'expected' => 8
];
$expand2 = [
    'type' => 'Expand crossing',
    'arr' => [1,3,8,6, 9,7,8,3],
    'expected' => 8
];

// align
$align0 = [
    'type' => 'Align',
    'arr' => [2,3,8,3, 7,5,6],
    'expected' => 5
];
$align1 = [
    'type' => 'Align',
    'arr' => [2,3,8,3, 15,5,6],
    'expected' => 5
];

$steps = [ $shrink0, $shrink1, $shrink2, $expand0, $expand1, $expand2, $align0, $align1];
foreach($steps as $step)
{

    // https://3v4l.org/MEbY5/perf#output test with array of 1001 number
//    echo 'Coordinate Check: ' . didCrossPreviousPathCoordinatesCehck($step).PHP_EOL;

    $res = didCrossPreviousPathPairsCheck($step['arr']);

    // https://3v4l.org/C7KAk/perf#output test with array of 1001 number
    echo "{$step['type']} : $res  expected {$step['expected']} - " . (($res == $step['expected']) ? "PASS" : "FAIL").PHP_EOL;

}
