<?php
//-*- coding: utf-8 -*-
// PHP標準の、GregorianToJD()、JDToGregorian() 関数、使いにくい
//   "月/日/年"の順序
//   Julian day number も、年月日も、整数*のみ*。少数(時間)は無視される
// -4714/11/25 = Julian Day number is 1
// GregorianToJD()、JDToGregorian() は、0(-4714/11/24) 以下は扱えない。全て0になる
//*************************************************
class Julian
{
    /**
     * Julian day number -> Modified Julian day number
     *
     * @param  float $jdnum ユリウス日
     * @return float    修正ユリウス日
     */
    static function JD2MJD($jdnum)
    {
        return ($jdnum - 2400000.5);
    }

    /**
     * Modified Julian day number -> Julian day number
     *
     * @param  float $mjdnum    修正ユリウス日
     * @return float    ユリウス日
     */
    static function MJD2JD($mjdnum)
    {
        return ($mjdnum + 2400000.5);
    }

    /**
     * Julian day number -> J2000.0 day number start from '2000-01-01 12:00:00'
     *
     * @param  float $jdnum ユリウス日
     * @return float    J2000.0日
     */
    static function JD2J2K($jdnum)
    {
        return ($jdnum - 2451545.0);
    }

    /**
     * J2000.0 -> Julian day number
     *
     * @param  float $mjdnum    J2000.0
     * @return float    ユリウス日
     */
    static function J2K2JD($mjdnum)
    {
        return ($mjdnum + 2451545.0);
    }

    /**
     * Gregorian date -> J2000.0 day
     *
     * @param  int $y0  西暦年
     * @param  int $m0  西暦月
     * @param  float $d0    西暦日
     * @param  float $h0    西暦時
     * @param  float $min0  西暦分
     * @param  float $s0    西暦秒
     * @return float    J2000.0日
     */
    static function G2J2K($y0, $m0, $d0, $h0 = 0, $min0 = 0, $s0 = 0)
    {
        $x = self::G2JD($y0, $m0, $d0, $h0, $min0, $s0);
        return( self::JD2J2K($x) );
    }

    /**
     * J2000.0 -> Gregorian date
     *
     * @param  float $j     J2000.0
     * @return array    [int 年, int 月, int 日, 'y'=>年, 'm'=>月, 'd'=>int 日, 'h'=>int 時, 's'=>float 秒]
     */
    static function J2K2G($j)
    {
        $x = J2K2JD($j);
        return( self::JD2G($x) );
    }

    // PHP has GregorianToJD($m, $d, $y), but it isn't useful
    /**
     * Gregorian date -> Julian day number
     *
     * @param  int $y0  西暦年
     * @param  int $m0  西暦月
     * @param  float $d0    西暦日
     * @param  float $h0    西暦時
     * @param  float $min0  西暦分
     * @param  float $s0    西暦秒
     * @return float    ユリウス日
     */
    static function G2JD0($y0, $m0, $d0, $h0 = 0, $min0 = 0, $s0 = 0)
    {
        $y = (int)$y0;
        $m = (int)$m0;
        $d = (float)$d0;
        $h = (float)$h0 ;
        $min = (float)$min0 ;
        $sec = (float)$s0 ;
        $errmsg="";
        if ($y < -4713) {
            $errmsg = $errmsg . "  year < -4713";
        }
        if ($m < 1 || $m > 12) {
            $errmsg = $errmsg . "  month < 1  or  month > 12";
        }
        if ($d < 0 || $d > 32) {
            $errmsg = $errmsg . "  day < 1  or  day > 31";
        }
        if ($h < -50 || $h > 50) {
            $errmsg = $errmsg . "  hour < 0";
        }
        if ($min < -120 || $min > 120) {
            $errmsg = $errmsg . "  min < 0";
        }
        if ($sec < -120 || $sec > 120) {
            $errmsg = $errmsg . "  sec < 0";
        }

        if (strlen($errmsg) > 0) {
            $errmsg = "ERROR: Julian::G2JD() : " . $errmsg . "\n";
            exit(1);
        }

        if (0 == $y) {
            $y = -1;
        }
        $jdInt = gregoriantojd($m, $d, $y); //BOOO!!!
        $f = $h / 24.0 + $min / 1440.0 + $sec / 86400.0;
        $jd = floatVal($jdInt) - 0.5 + $f;

        return($jd);
    }

    // G2JD1  can calculate before -4714/11/25(=1JD).
    static function G2JD($y0, $m0, $d0, $h0 = 0, $min0 = 0, $s0 = 0)
    {
        $y = (int)$y0;
        $m = (int)$m0;
        $d = (float)$d0;
        $h = (float)$h0 ;
        $min = (float)$min0 ;
        $sec = (float)$s0 ;
        $errmsg="";
        if ($m < 1 || $m > 12) {
            $errmsg = $errmsg . "  month < 1  or  month > 12";
        }
        if ($d < 0 || $d > 32) {
            $errmsg = $errmsg . "  day < 1  or  day > 31";
        }
        if ($h < -50 || $h > 50) {
            $errmsg = $errmsg . "  hour < 0";
        }
        if ($min < -120 || $min > 120) {
            $errmsg = $errmsg . "  min < 0";
        }
        if ($sec < -120 || $sec > 120) {
            $errmsg = $errmsg . "  sec < 0";
        }
        if (strlen($errmsg) > 0) {
            $errmsg = "ERROR: Julian::G2JD() : " . $errmsg . "\n";
            exit(1);
        }

        /*
          $y0 < 0 , $y0 > 0   "0 AD." is not exists.
        */
        if (0==$y) {
            $y = -1;
        }

        /*
          but in following caluculation, $y assume that "1AD"=1, "1BC"=0, "2BC"=-1
         */
        if ($y < 1) {
            $y = $y + 1;
        }
        $a = intval((14 - $m)/12);
        $y = $y + 4800 - $a;
        $m = $m + 12*$a -3;

        $jdn = $d + intval((153*$m +2)/5) + 365*$y + intval($y/4) - intval($y/100) + intval($y/400) - 32045;

        $f = $h / 24.0 + $min / 1440.0 + $sec / 86400.0;
        $jd = floatVal($jdn) + $f -0.5;

        return($jd);
    }

    /**
     * Gregorian date -> Modified Julian day number
     *
     * @param  int $y0  西暦年
     * @param  int $m0  西暦月
     * @param  float $d0    西暦日
     * @param  float $h0    西暦時
     * @param  float $min0  西暦分
     * @param  float $s0    西暦秒
     * @return float    修正ユリウス日
     */
    static function G2MJD($y0, $m0, $d0, $h0 = 0, $min0 = 0, $s0 = 0)
    {
        $j=self::G2JD($y0, $m0, $d0, $h0, $min0, $s0);
        return ($j - 2400000.5);
    }

    // PHP has JDToGregorian($jd), but it isn't useful
    /**
     * Julian day number -> Gregorio date
     *
     * @param  float $jdnum0    ユリウス日
     * @return array    [int 年, int 月, int 日, 'y'=>年, 'm'=>月, 'd'=>int 日, 'h'=>int 時, 'min'=>int 分, 's'=>float 秒]
     */
    static function JD2G0($jdnum0)
    {
        $jdnum = (float)$jdnum0;

        if ($jdnum <= 0) {
            print "ERROR: Julian::JD2G($jdnum) :  jdnum <= 0\n";
            exit(1);
        }

        $jdnum = $jdnum +0.5;
        $jdInt = intVal($jdnum);
        $gstr = jdtogregorian($jdInt); // month/day/year
        $dx = explode('/', $gstr);  // [m,d,y]
        $dd = array( intval($dx[0]), intval($dx[1]), intval($dx[2]) );

        $f = $jdnum - intVal($jdnum);
        if ($f < 0) {
            $f = 1.0 + $f;
        }

        $x = $f * 24.0;
        $h = intVal($x);

        $x = $x - $h;
        $x = $x * 60.0;
        $min = intVal($x);

        $x = $x - $min;
        $x = $x * 60.0;
        $sec = $x;

        return array($dd[2], $dd[0], $dd[1],
                     'y'=>$dd[2], 'm'=>$dd[0], 'd'=>$dd[1],
             'h'=>intval($h), 'min'=>intval($min), 's'=>$sec);
    }

    //  can calculate before -4714/11/25(=1JD).
    static function JD2G($jdnum0)
    {
        $jdnum = (float)$jdnum0;
        $jdi = intval($jdnum);
        $jdf = $jdnum - $jdi;

        // HH:MM:SS
        $f = $jdnum+0.5 -intval($jdnum+0.5);
        if ($f < 0) {
            $f = 1.0 + $f;
        }

        $x = $f * 24.0;
        $hour = intVal($x);

        $x = $x - $hour;
        $x = $x * 60.0;
        $min = intVal($x);

        $x = $x - $min;
        $x = $x * 60.0;
        $sec = $x;

        // year,month,day
        $j = intval($jdnum + 0.5);
        if ($j >= 0) {
        } else {
/*
        |     day=-1      |  day=0  |
        +--------+--------+----+----+---
      -1.5     -1.0     -0.5  0.0  0.5
float -0.5      0.0     -0.5
+0.5   0.0      0.5      0.0
         0.0~0.5    -0.4999~-0.00001
*/
            $a = $jdf + 0.5;
            if ($a >= 0.0) {
                $j = $jdi;
            } else {
                $j = $jdi -1;
            }
        }

        $cj = 1401;
        $cp = 1461;
        $cm = 2;
        $cn = 12;
        $cr = 4;
        $cs = 153;
        $cu = 5;
        $cw = 2;
        $cv = 3;
        $cy = 4716;
        $cB = 274277;
        $cC = -38;
        $f = $j + $cj + intval((intval((4*$j + $cB)/146097) * 3) / 4) + $cC;
        $e = $cr * $f + $cv;
        $g = intval(($e % $cp) / $cr);
        $h = $cu * $g + $cw;
        $day = intval(($h % $cs) / $cu) + 1;
        $mon = (intval($h / $cs) + $cm) % $cn + 1;
        $year = intval($e / $cp) - $cy + intval(($cn + $cm - $mon) / $cn);
        if ($year <= 0) {
            $year = $year -1;
        }

        return array($year, $mon, $day, 'y'=>$year, 'm'=>$mon, 'd'=>$day, 'h'=>intval($hour), 'min'=>intval($min), 's'=>$sec);
    }

    /**
     * Modified Julian day number -> Gregorio date
     *
     * @param  float $mjdnum0   修正ユリウス日
     * @return array    [int 年, int 月, int 日, 'y'=>年, 'm'=>月, 'd'=>int 日, 'h'=>int 時, 's'=>float 秒]
     */
    static function MJD2G($mjdnum0)
    {
        $jdnum = (float)$mjdnum0 + 2400000.5;
        return( self::JD2G($jdnum) );
    }
} // end of class
