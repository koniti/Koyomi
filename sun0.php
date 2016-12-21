<?php
//-*- coding: utf-8 -*-
//*************************************
// 太陽の位置(太陽黄経) = つまるところ、地球の公転軌道の計算
class SunCommon
{

    /**
     * 0 <= 角度 <= 360 に
     *
     * @access protected
     * @param  float $deg0  角度
     * @return float    角度
     */
    protected function nm($deg0)
    {
        $deg = (float)$deg0;
        while ($deg < 0) {
            $deg = $deg + 360;
        }
        while ($deg > 360) {
            $deg = $deg - 360;
        }
        return $deg;
    }

    /**
     * 24節気の切り替えが起こる角度、使いにくいので、ずらす。1月が最も小さくなるように。
     *
     * @access protected
     * @param  float $deg0  角度
     * @return float    角度
     */
    protected function easy2use24deg($deg0)
    {
        $deg = (float)$deg0;
        // array('', 285, 315, 345,  15,  45,  75, 105, 135, 165, 195, 225, 255);
        // array('', 390, 420, 450, 120, 150, 180, 210, 240, 270, 300, 330, 360);
        // array('',  30,  60,  90, 120, 150, 180, 210, 240, 270, 300, 330, 360);
        $a = $deg + 105;
        while ($a < 0.0) {
            $a = $a + 360.0;
        }
        while ($a > 360.0) {
            $a = $a - 360.0;
        }
        return $a;
    }
} // end of class SunCommon
