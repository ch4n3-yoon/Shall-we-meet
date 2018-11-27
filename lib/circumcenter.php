<?php
/**
 * Created by PhpStorm.
 * User: ch4n3
 * Date: 2018-11-27
 * Time: 오전 11:17
 *
 * 외심 구하는 코드
 *
 */

function make_pair($x, $y) {
    return Array('x' => $x, 'y' => $y);
}


function lineFromPoints($P, $Q, &$a,
                        &$b, &$c)
{
    $a = $Q['y'] - $P['y'];
    $b = $P['x'] - $Q['x'];
    $c = $a*($P['x'])+ $b*($P['y']);
}

function perpendicularBisectorFromLine($P, $Q,
                 &$a, &$b, &$c)
{
    $mid_point = Array( 'x' => ($P['x'] + $Q['x'])/2,
                        'y' => ($P['y'] + $Q['y'])/2);

    // c = -bx + ay
    $c = -$b*($mid_point['x']) + $a*($mid_point['y']);

    $temp = $a;
    $a = -$b;
    $b = $temp;
}

// Returns the intersection point of two lines
function lineLineIntersection($a1, $b1, $c1,
                         $a2, $b2, $c2)
{
    $determinant = $a1*$b2 - $a2*$b1;
    if ($determinant == 0)
    {
        // The lines are parallel. This is simplified
        // by returning a pair of FLT_MAX
        return make_pair(INF, INF);
    }

    else
    {
        $x = ($b2*$c1 - $b1*$c2)/$determinant;
        $y = ($a1*$c2 - $a2*$c1)/$determinant;
        return make_pair($x, $y);
    }
}

function findCircumCenter($P, $Q, $R)
{
    // Line PQ is represented as ax + by = c
    $a = 0.0; $b = 0.0; $c = 0.0;
    lineFromPoints($P, $Q, $a, $b, $c);

    // Line QR is represented as ex + fy = g
    $e = 0.0; $f = 0.0; $g = 0.0;
    lineFromPoints($Q, $R, $e, $f, $g);

    // Converting lines PQ and QR to perpendicular
    // vbisectors. After this, L = ax + by = c
    // M = ex + fy = g
    perpendicularBisectorFromLine($P, $Q, $a, $b, $c);
    perpendicularBisectorFromLine($Q, $R, $e, $f, $g);

    // The point of intersection of L and M gives
    // the circumcenter
    $circumcenter =
    lineLineIntersection($a, $b, $c, $e, $f, $g);

    if ($circumcenter['x'] == INF &&
        $circumcenter['y'] == INF)
    {
        return false;
    }

    else
    {
        return $circumcenter;
    }
}

//var_dump(findCircumCenter(Array('x' => 10, 'y'=>1), Array('x' => 5, 'y'=>-6), Array('x' => -6, 'y'=>1)));

