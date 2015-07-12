<?php
//-*- coding: utf-8 -*-
require_once('common.php');

//--- test code
echo " Y= ";
$line = trim(fgets(STDIN));
$y = intval($line);
echo " M= ";
$line = trim(fgets(STDIN));
$m = intval($line);
echo " D= ";
$line = trim(fgets(STDIN));
$d = intval($line);

/*
echo " h= ";
$line = trim(fgets(STDIN));
$h = floatval($line);
*/
$h = 6;
$min = 0;
$sec = 0;

$jisa=9.0;

echo doCalc($jisa, $y,$m,$d, $h,$min,$sec);

exit(0);
//--- test code end

function doCalc($jisa0, $y0,$m0,$d0, $h0=0, $min0=0, $sec0=0)
{
    $jisa = (float)$jisa0;
    $y = (int)$y0;
    $m = (int)$m0;
    $d = (int)$d0;
    $h = (float)$h0;
    $min = (float)$min0;
    $sec = (float)$sec0;

    $res_str="";

    $py=$y; $ny=$y;
    $pm = $m -1;
    if ($pm<1) { $py = $y -1; $pm=12; }
    $nm = $m +1;
    if ($nm>12) { $ny = $y +1; $nm=1; }

    $res_str = sprintf("%d-%02d-%02d %02d:%02d:%09.6f\n", $y ,$m ,$d ,$h,$min,$sec);

    $k = new Koyomi($jisa);
    $j = Julian::G2JD($y, $m, $d, $h, $min, $sec);
    $a = $k->JDto91012($j);
    $s9listAll = $k->getlist9(); $s9list = $s9listAll['sh'];
    $s10list = $k->getlist10();
    $s12list = $k->getlist12();
    $s6list = $k->getlist6();

    $y9  = $a['ynum9'];
    $y10 = $a['ynum10'];
    $y12 = $a['ynum12'];
    $ynatt = $k->n1012toNatt($y10,$y12);
    $ytenkai = $k->n1912Tenkai($y10,$y12);

    $m9  = $a['mnum9'];
    $m10 = $a['mnum10'];
    $m12 = $a['mnum12'];
    $mnatt = $k->n1012toNatt($m10,$m12);
    $mtenkai = $k->n1912Tenkai($m10,$m12);

    $d9  = $a['dnum9'];
    $d10 = $a['dnum10'];
    $d12 = $a['dnum12'];
    $dnatt = $k->n1012toNatt($d10,$d12);
    $dtenkai = $k->n1912Tenkai($d10,$d12);

    $res_str = $res_str . "年: {$s10list[$y10]} {$s12list[$y12]} {$s9list[$y9]} {$ynatt} {$ytenkai}\n";
    $res_str = $res_str . "月: {$s10list[$m10]} {$s12list[$m12]} {$s9list[$m9]} {$mnatt} {$mtenkai}\n";
    $res_str = $res_str . "日: {$s10list[$d10]} {$s12list[$d12]} {$s9list[$d9]} {$dnatt} {$dtenkai}\n";

if (HAVE_ASTRO_MOON): // 月 天文計算あり
    $qk = $k->JD2Q($j); $dnum6 = $qk['dnum6']; $mnum6 = intval($qk['qm']) % 6 + 1;
    $res_str = $res_str . sprintf("旧暦日: %d-%02d-%02d　　日の六曜=%s(%d)\n",
            $qk['qy'],$qk['qm'],$qk['qd'],
            $s6list[$dnum6], $dnum6);
endif;

if (HAVE_ASTRO_SUN): // 太陽 天文計算あり
    $sun = $k->getlist24deg();
    $res_str = $res_str . "先月の月替わり: ";
    $deg = $sun[$pm];
    $s = Julian::G2JD($py, $pm, 1, 0.0) - $jisa/24.0;
    $e = $s + 10;;
    $j = Sun::searchDegDay($deg, $s, $e) + $jisa/24.0;
    $g = Julian::JD2G($j);
    $res_str = $res_str .sprintf("%d-%02d-%02d %02d:%02d:%09.6f\n", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);

    $res_str = $res_str . "今月の月替わり: ";
    $deg = $sun[$m];
    $s = Julian::G2JD($y, $m, 1, 0.0) - $jisa/24.0;
    $e = $s + 10;
    $j = Sun::searchDegDay($deg, $s, $e) + $jisa/24.0;
    $g = Julian::JD2G($j);
    $res_str = $res_str . sprintf("%d-%02d-%02d %02d:%02d:%09.6f\n", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);

    $res_str = $res_str . "来月の月替わり: ";
    $deg = $sun[$nm];
    $s = Julian::G2JD($ny, $nm, 1, 0.0) - $jisa/24.0;
    $e = $s + 10;
    $j = Sun::searchDegDay($deg, $s, $e) + $jisa/24.0;
    $g = Julian::JD2G($j);
    $res_str = $res_str . sprintf("%d-%02d-%02d %02d:%02d:%09.6f\n", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);

    $res_str = $res_str . "土用:\n";
    $a = $k->yDoyou($y);
    foreach ($a as $g) {
        $res_str = $res_str . sprintf("    %d-%02d-%02d %02d:%02d:%09.6f\n", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);
    }
endif;

    return($res_str);
}
?>
