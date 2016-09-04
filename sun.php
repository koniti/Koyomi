<?php
//-*- coding: utf-8 -*-
require_once('common.php');
require_once('sun0.php');

// 太陽の位置(太陽黄経)
class Sun extends SunCommon
{
    /**
     * $deg0 になる時の、JD(UTC) を返す
     * $low0 - $high0 日(JD UTC) の間を調べる。
     * 時差は、呼び出す前に考慮すること。返り値に時差を足すこと。
     *
     * @param  float $deg0	λsun°(0〜360)
     * @param  float $low0	ユリウス日(解を探す期間)
     * @param  float $high0	ユリウス日(解を探す期間)
     * @param  int   $ncount	Loopのカウンター
     * @return float 	ユリウス日
     */
    function searchDegDay($deg0, $low0, $high0, $ncount=0)
    {
        return 0;
    }

} // end of class
?>
