<?php
//-*- coding: utf-8 -*-
require_once('common.php');
//*************************************************
class Moon
{
    /**
     * 新月の日時を求める(UTC)
     * 天文計算の部分を作ること
     *
     * @param  float $low0	ユリウス日(解を探す期間)
     * @param  float $high0	ユリウス日(解を探す期間)
     * @param  int $mode	0:新月計算 / 1:満月計算
     * @return float[] 	ユリウス日
     */
    function findNewMoon($low0, $high0, $mode=0)
    {
        return 0;
    }


} // end of class
?>
