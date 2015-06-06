<?php
//-*- coding: utf-8 -*-
// PHP標準の、GregorianToJD()、JDToGregorian() 関数、使いにくい
//   "月/日/年"の順序
//   Julian day number も、年月日も、整数*のみ*。少数(時間)は無視される
//*************************************************
class Julian
{
    /**
     * Julian day number -> Modified Julian day number
     *
     * @param  float $jdnum	ユリウス日
     * @return float 	修正ユリウス日
     */
    function JD2MJD($jdnum)
    {
        return ($jdnum - 2400000.5);
    }

    /**
     * Modified Julian day number -> Julian day number
     *
     * @param  float $mjdnum	修正ユリウス日
     * @return float 	ユリウス日
     */
    function MJD2JD($mjdnum)
    {
        return ($mjdnum + 2400000.5);
    }

    /**
     * Julian day number -> J2000.0 day number start from '2000-01-01 12:00:00'
     *
     * @param  float $jdnum	ユリウス日
     * @return float 	J2000.0日
     */
    function JD2J2K($jdnum)
    {
        return ($jdnum - 2451545.0);
    }

    /**
     * J2000.0 -> Julian day number
     *
     * @param  float $mjdnum	J2000.0
     * @return float 	ユリウス日
     */
    function J2K2JD($mjdnum)
    {
        return ($mjdnum + 2451545.0);
    }

    /**
     * Gregorian date -> J2000.0 day
     *
     * @param  int $y0	西暦年
     * @param  int $m0	西暦月
     * @param  float $d0	西暦日
     * @param  float $h0	西暦時
     * @param  float $min0	西暦分
     * @param  float $s0	西暦秒
     * @return float 	J2000.0日
     */
    function G2J2K($y0, $m0, $d0, $h0=0, $min0=0, $s0=0)
    {
        $x = self::G2JD($y0, $m0, $d0, $h0, $min0, $s0);
        return( self::JD2J2K($x) );
    }

    /**
     * J2000.0 -> Gregorian date
     *
     * @param  float $j 	J2000.0
     * @return array	[int 年, int 月, int 日, 'y'=>年, 'm'=>月, 'd'=>int 日, 'h'=>int 時, 's'=>float 秒]
     */
    function J2K2G($j)
    {
        $x = J2K2JD($j);
        return( self::JD2G($x) );
    }

    // PHP has GregorianToJD($m, $d, $y), but it isn't useful
    /**
     * Gregorian date -> Julian day number
     *
     * @param  int $y0	西暦年
     * @param  int $m0	西暦月
     * @param  float $d0	西暦日
     * @param  float $h0	西暦時
     * @param  float $min0	西暦分
     * @param  float $s0	西暦秒
     * @return float 	ユリウス日
     */
    function G2JD($y0, $m0, $d0, $h0=0, $min0=0, $s0=0)
    {
        $y = (int)$y0;
        $m = (int)$m0;
        $d = (float)$d0;
        $h = (float)$h0 ; $min = (float)$min0 ; $sec = (float)$s0 ;
        $errmsg="";
        if ($y < -4713) { $errmsg = $errmsg . "  year < -4713"; }
        if ($m < 1 || $m > 12) { $errmsg = $errmsg . "  month < 1  or  month > 12"; }
        if ($d < 0 || $d > 32) { $errmsg = $errmsg . "  day < 1  or  day > 31"; }
        if ($h < -50 || $h > 50) { $errmsg = $errmsg . "  hour < 0"; }
        if ($min < -120 || $min > 120) { $errmsg = $errmsg . "  min < 0"; }
        if ($sec < -120 || $sec > 120) { $errmsg = $errmsg . "  sec < 0"; }
        if (strlen($errmsg) > 0) {
            $errmsg = "ERROR: Julian::G2JD() : " . $errmsg . "\n";
            exit(1);
        }

        // start at 00:00:00
        $d = $d - 0.5 + $h/24 + $min/(24*60) + $sec/(24*60*60);

        //---
        $a = intval( (14-$m)/12 ); // 1月2月は1。それ以外は0
        $y = $y0 + 4800 - $a; // we will start counting years from the year -4800.
        $m = $m0 + 12 * $a - 3; // 10->1月, 11->2月, 0->3月, 1->4月...
        $jd = $d + floor( (153*$m +2)/5 ) + 365*$y + floor($y / 4) - floor($y/100) + floor($y/400) -32045;

        return($jd);
        /*
          note:
          CE,BCE counting:  year 1 == 1 CE / year 0 == 1 BCE / year -1 == 2 BCE
          integer division (153*$m +2)/5 : day counting. calculate the number of days in the previous months
          y/4 - y/100 + y/400 (all integer divisions) : calculates the number of leap years since the year -4800 (which corresponds to a value of 0 for y)
          -32045 : ensures that the result will be 0 for January 1, 4713 BCE
        */
    }

    /**
     * Gregorian date -> Modified Julian day number
     *
     * @param  int $y0	西暦年
     * @param  int $m0	西暦月
     * @param  float $d0	西暦日
     * @param  float $h0	西暦時
     * @param  float $min0	西暦分
     * @param  float $s0	西暦秒
     * @return float 	修正ユリウス日
     */
    function G2MJD($y0, $m0, $d0, $h0=0, $min0=0, $s0=0)
    {
        $j=self::G2JD($y0, $m0, $d0, $h0, $min0, $s0);
        return ($j - 2400000.5);
    }

    // PHP has JDToGregorian($jd), but it isn't useful
    /**
     * Julian day number -> Gregorio date
     *
     * @param  float $jdnum0	ユリウス日
     * @return array	[int 年, int 月, int 日, 'y'=>年, 'm'=>月, 'd'=>int 日, 'h'=>int 時, 's'=>float 秒]
     */
    function JD2G($jdnum0)
    {
        $jdnum = (float)$jdnum0;
        if ($jdnum < 0) {
            print "ERROR: Julian::JD2G(jdnum) :  jdnum < 0\n";
            exit(1);
        }

        // start at 00:00:00
        $jdnum = (float)$jdnum0 + 0.5;

        //---
        // constant values of the expressions
        $y = 4716; $v = 3;
        $j = 1401; $u = 5;
        $m = 2;    $s = 153;
        $n = 12;   $w = 2;
        $r = 4;    $b = 274277;
        $p = 1461; $c = -38;

        // let's computing.
        $f = $jdnum + $j + intval(  (intval((4 * $jdnum + $b) / 146097) * 3)  / 4  ) + $c;
        $e = $r * $f + $v;
        $g = intval( ($e % $p) / $r );
        $h = $u * $g + $w;
        $D = intval( ($h % $s) / $u ) + 1;
        $M = (intval($h / $s) + $m) % $n + 1;
        $Y = intval($e / $p) - $y + intval( ($n + $m - $M) / $n);

        // hour,min,sec
        $decimal = $jdnum - fn_cut_decimal($jdnum);
        $hour = $decimal * 24.0;
        $min = ($hour - intval($hour)) * 60.0;
        $sec = ($min - intval($min)) * 60.0;

        return array($Y, $M, $D, 'y'=>$Y, 'm'=>$M, 'd'=>$D, 'h'=>intval($hour), 'min'=>intval($min), 's'=>$sec);
    }

    /**
     * Modified Julian day number -> Gregorio date
     *
     * @param  float $mjdnum0	修正ユリウス日
     * @return array	[int 年, int 月, int 日, 'y'=>年, 'm'=>月, 'd'=>int 日, 'h'=>int 時, 's'=>float 秒]
     */
    function MJD2G($mjdnum0)
    {
        $jdnum = (float)$mjdnum0 + 2400000.5;
        return( self::JD2G($jdnum) );
    }
} // end of class
?>