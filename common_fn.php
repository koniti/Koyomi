<?php
//-*- coding: utf-8 -*-
//*************************************************
/**
 * keep 'float' type. intval() or (int) cast returns int.
 * -4.5 は -4 にしたいよね。floor(-4.5)=-5 になる。
 *
 * @param  float|int|string $x0	数値
 * @return float 	小数点以下をカットした値
 */
function fn_cut_decimal($x0)
{
    $x = (float)$x0;
    if ($x < 0.0) {
        return ceil($x);
    }
    else {
        return floor($x);
    }
}

/**
 * 角度の正規化
 *
 * @param  float $angle	角度
 * @return float 	角度 0≦θ＜360
 */
function fn_nm($angle) {
        if ( $angle < 0 ) {
            $angle1 = -$angle;
            $angle2 = fn_cut_decimal( $angle1 / 360 );
            $angle1 -= 360 * $angle2;
            $angle1 = 360 - $angle1;
        } else {
            $angle1 = fn_cut_decimal( $angle / 360 );
            $angle1 = $angle - 360 * $angle1;
        }
        return($angle1);
}

?>
