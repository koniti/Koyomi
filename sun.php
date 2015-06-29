<?php
//-*- coding: utf-8 -*-
require_once('common.php');
require_once('sun0.php');

// 太陽の位置(太陽黄経) = つまるところ、地球の公転軌道の計算
class Sun extends SunCommon
{
    /**
     * $deg0 になる時の、JD(UTC) を返す
     * $low0 - $high0 日(JD UTC) の間を調べる。
     * 時差は、呼び出す前に考慮すること。返り値に時差を足すこと。
     * ここは数学的にもっと良い物に変えたい。今は力技。
     * あるいは外部プログラムを呼び出してやってもよい。その場合は中身をごっそり変える
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

        $deg  = abs((float)$deg0); $deg = self::nm($deg);
        $low  = abs((float)$low0);
        $high = abs((float)$high0);
        if ($high < $low) { $a = $high; $high = $low; $low = $a; }

        $p    = ($high + $low)/2.0;
        $lambda = self::JD2Lambda($p); $lambda = self::nm($lambda);

        $deltaD = $lambda - $deg;
        if (abs($deltaD)>180) {
            if ($lambda < $deg) { $lambda = $lambda + 360; }
            else { $deg = $deg + 360; }
            $deltaD = $lambda - $deg;
        }

        if (abs($deltaD) < Hantei1) {
            return($p);
        }

        if ($ncount > 99) {
            echo "ERROR: searchDegDay() : calculation is loop\n";
            echo "       deg=$deg JD=$p jdlow=$low jdhigh=$high lambda=$lambda\n";
            exit(1);
            //return $deg;
        }
        $ncount = $ncount + 1;

        if ($deltaD < 0) {
            if ($p >= $high) { $p = ($p + $low)/2.0; }
            $a = self::searchDegDay($deg, $p, $high, $ncount);
        }
        else {
            if ($p <= $low) { $p = ($p + $high)/2.0; }
            $a = self::searchDegDay($deg, $low, $p, $ncount);
        }
        return $a;
    }

} // end of class
?>
