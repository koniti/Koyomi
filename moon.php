<?php
//-*- coding: utf-8 -*-
require_once('common.php');
//*************************************************
class Moon
{
    /**
     * 新月の日時を求める
     * 天文計算の部分を作ること
     *
     * @param  float $jd0	ユリウス日
     * @param  float $jisa	時差(単位=時)
     * @return float 	ユリウス日
     */
    function findNewMoon($jd0, $jisa)
    {
        return 0;
    }

    /**
     * 月齢の計算。ユリウス日の月齢は？
     *
     * @link http://fujikoweb.net
     * @param  float $tm0	ユリウス日
     * @param  float $jisa	時差(単位=時)
     * @return float 	月齢
     */
    function  moonAge($tm, $jisa)
    {
        return 0;
    }

} // end of class
?>
