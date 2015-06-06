<?php
//-*- coding: utf-8 -*-
require_once('common.php');
/* Sun Longitude
 1月 小寒 285deg	大寒 300
 2月 立春 315   	雨水 330
 3月 啓蟄 345   	春分   0=360
 4月 清明  15   	穀雨  30
 5月 立夏  45   	小満  60
 6月 芒種  75   	夏至  90
 7月 小暑 105   	大暑 120
 8月 立秋 135   	処暑 150
 9月 白露 165   	秋分 180
10月 寒露 195   	霜降 210
11月 立冬 225   	小雪 240
12月 大雪 255   	冬至 270

夏至  90 degs ; 06月 陽遁 癸亥九紫 -> 甲子九紫、陰遁へ
冬至 270 degs ; 12月 陰遁 癸亥一白 -> 甲子一白、陽遁へ
夏至またはその前後に甲午がある場合は、その甲午を三碧として陰遁を始める。
冬至またはその前後に甲午がある場合は、その甲午を七赤として陽遁を始める。
ただし、かなり幅がある。きっちり夏至冬至前後数日では切り替わらない。夏至なら、5月末〜7月末までの間で切り替わる。

土曜 27,117,207,297度から。
*/

/*
日の九星(日家九星)、特殊な切り替わりの日
 MJD   date
21220 1916-12-23 七甲午
25420 1928-06-23 三甲午 +4200日 = 60*70 (MJD+8180)%8400=0 (MJD+3980)%4200=0
29620 1939-12-23 七甲午 +4200日         (MJD+3980)%8400=0 (MJD+3980)%4200=0
33820 1951-06-23 三甲午 +4200日
38020 1962-12-22 七甲午 +4200日
42220 1974-06-22 三甲午 +4200日
46420 1985-12-21 七甲午 +4200日
50620 1997-06-21 三甲午 +4200日
54820 2008-12-20 七甲午 +4200日

通常の切り替えと、特異点のちがい
 MJD   date
54250 2007-05-30 九甲子
54430 2007-11-26 一甲子 +180日 = 60*3
54610 2008-05-24 九甲子 +180日
54820 2008-12-20 七甲午 +210日 = 60*3+30 <<特異点
55030 2009-07-18 九甲子 +210日 = 60*3+30
55210 2010-01-14 一甲子 +180日
55390 2010-07-13 九甲子 +180日
55570 2011-01-09 一甲子 +180日
55750 2011-07-08 九甲子 +180日
*/

/*
Numbering
作成中ややこしくなったので、0スタートはやめた。
∵丑は1月だし、先勝は1だし、一白は1だしで。
0スタートのほうが余分なことしなくて良いのだが。

一白水星 = 1, 二黒土星 = 2, 三碧木星 = 3, 四緑木星 = 4,
五黄土星 = 5,
六白金星 = 6, 七赤金星 = 7, 八白土星 = 8, 九紫火星 = 9
丑=1, 寅=2, 卯=3, 辰=4, 巳=5, 午=6, 未=7, 申=8, 酉=9, 戌=10, 亥=11, 子=12
甲=1, 乙=2, 丙=3, 丁=4, 戊=5, 己=6, 庚=7, 辛=8, 壬=9, 癸=10
先勝=1, 友引=2, 先負=3, 仏滅=4, 大安=5, 赤口=6
60干支番号=1-60, 納音=1-30
*/


//****************************
class Koyomi
{
    protected $jisa; //太陽の位置を計算するとき、時差が必要。他は、Local Time でOK
    protected static $kan10 = array('', '甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸' );
    protected static $si12  = array('子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子');
    protected static $star9 = array('', '一白水星', '二黒土星', '三碧木星', '四緑木星', '五黄土星', '六白金星', '七赤金星', '八白土星', '九紫火星' );
    protected static $star9sh = array('', '一白', '二黒', '三碧', '四緑', '五黄', '六白', '七赤', '八白', '九紫' );
    protected static $star9vs = array('', '一', '二', '三', '四', '五', '六', '七', '八', '九' );
    protected static $yo6 = array('', '先勝', '友引', '先負', '仏滅', '大安', '赤口' );
    protected static $mlam = array('', 285, 315, 345, 15, 45, 75, 105, 135, 165, 195, 225, 255);
    protected static $mchu = array('', 300, 330,   0, 30, 60, 90, 120, 150, 180, 210, 240, 270);
    protected static $natt = array('',
                                   '海中金',	//かいちゅうきん	甲子・乙丑
                                   '爐中火',	//ろちゅうか 	丙寅・丁卯
                                   '大林木',	//たいりんぼく 	戊辰・己巳
                                   '路傍土',	//ろぼうど 	庚午・辛未
                                   '剣鋒金',	//けんぼうきん 	壬申・癸酉

                                   '山頭火',	//さんとうか 	甲戌・乙亥
                                   '澗下水',	//かんげすい 	丙子・丁丑
                                   '城頭土',	//じょうとうど 	戊寅・己卯
                                   '白鑞金',	//はくろうきん 	庚辰・辛巳
                                   '楊柳木',	//ようりゅうぼく 	壬午・癸未

                                   '井泉水',	//せいせんすい 	甲申・乙酉
                                   '屋上土',	//おくじょうど 	丙戌・丁亥
                                   '霹靂火',	//へきれきか 	戊子・己丑
                                   '松柏木',	//しょうはくぼく 	庚寅・辛卯
                                   '長流水',	//ちょうりゅうすい	壬辰・癸巳

                                   '沙中金',	//さちゅうきん 		甲午・乙未
                                   '山下火',	//さんげか 		丙申・丁酉
                                   '平地木',	//へいちぼく 		戊戌・己亥
                                   '壁上土',	//へきじょうど 		庚子・辛丑
                                   '金箔金',	//きんぱくきん 		壬寅・癸卯

                                   '覆燈火',	//ふくとうか 		甲辰・乙巳
                                   '天河水',	//てんかすい 		丙午・丁未
                                   '大駅土',	//だいえきど 		戊申・己酉
                                   '釵釧金',	//さいせんきん 		庚戌・辛亥
                                   '桑柘木',	//そうたくぼく 		壬子・癸丑

                                   '大溪水',	//たいけいすい 		甲寅・乙卯
                                   '沙中土',	//さちゅうど 		丙辰・丁巳
                                   '天上火',	//てんじょうか 		戊午・己未
                                   '柘榴木',	//たくりゅうぼく 		庚申・辛酉
                                   '大海水' 	//たいかいすい 		壬戌・癸亥
                                   );

    function __construct($jisa0=0.0) {
        $this->jisa = (float)$jisa0 / 24.0;
    }
    function setTimeDiff($jisa0=0.0) {
        $this->jisa = (float)$jisa0 / 24.0;
    }
    function getTimeDiff() {
        return $this->jisa;
    }
    protected function toLocalJD($jd0) {
        return( (float)$jd0 + $this->jisa );
    }
    protected function toUTCJD($jd0) {
        return( (float)$jd0 - $this->jisa );
    }

    // Julian day number -> 日の十干
    // この手の計算だと、JDは、0.5 がついてまわるので、修正ユリウス日を使うほうが、やりやすい。
    function JDto10kan($jd)
    {
        $mjd = Julian::JD2MJD((float)$jd);
        return( self::MJDto10kan($mjd) );
    }
    // M Julian day number -> 日の十干
    function MJDto10kan($mjd)
    {
        // MJD を 10で割ったあまりが、以下のように対応
        // 余り: 0  1  2  3  4  5  6  7  8  9
        // 十干: 甲 乙 丙 丁 戊 己 庚 辛 壬 癸
        //$a=array('甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸');
        $b = array( 1,    2,    3,    4,    5,    6,    7,    8,    9,    10);
        $x = $mjd % 10;
        while ($x<0) { $x = $x + 10; }
        $num = $b[$x];  // $num = $x +1; という計算でも良いが、見やすさ優先で
        $s = sprintf("%s", self::$kan10[$num]);
        return( array('str'=>$s, 'num'=>$num) );
    }

    // Julian day number -> 日の十二支
    function JDto12si($jd)
    {
        $mjd = Julian::JD2MJD((float)$jd);
        return( self::MJDto12si($mjd) );
    }
    // M Julian day number -> 日の十二支
    function MJDto12si($mjd)
    {
        // MJD を 12で割ったあまりが、以下のように対応
        // 余り: 0  1  2  3  4  5  6  7  8  9  10 11
        // 十二: 寅 卯 辰 巳 午 未 申 酉 戌 亥 子 丑
        //$a=array('寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑');
        $b = array( 2,    3,    4,    5,    6,    7,    8,    9,    10,   11,   12,   1);
        $x = $mjd % 12;
        while ($x<0) { $x = $x + 12; }
        $num = $b[$x]; // $num = ($x + 1) % 12 + 1; という計算でもよいが、見やすさ優先で
        $s = sprintf("%s", self::$si12[$num]);
        return( array('str'=>$s, 'num'=>$num) );
    }

    //ちなみに、曜日
    //余り: 0  1  2  3  4  5  6
    //曜日: 水 木 金 土 日 月 火
    function MJDtoW($mjd)
    {
        $tbl = array('水', '木', '金', '土', '日', '月', '火');
        $x = $mjd % 7;
        return( $tbl[$x] );
    }

    // Julian day number -> 年の9,10,12を求める
    function JDtoYearStars($jd0)
    {
        $jd = (float)$jd0;
        // ***年***
        // 01-01 〜 02-03 一杯までは前年扱い。その分、日数(固定値34日)を引いて計算すると楽。本当は2/4も24節気計算しないといけないのだが。100年中何回か2/5(or2/3)が切り替えの時がある
        $x = $jd - 34;
        $ar = Julian::JD2G($x);
        $y = $ar['y'];

        // $a9=array('二', '一', '九', '八', '七', '六', '五', '四', '三');
        $b9  = array( 2,    1,    9,    8,    7,    6,    5,    4,    3);
        //$a10=array('庚', '辛', '壬', '癸', '甲', '乙', '丙', '丁', '戊', '己');
        $b10 = array( 7,    8,    9,    10,   1,    2,    3,    4,    5,    6);
        //$a12=array('申', '酉', '戌', '亥', '子', '丑', '寅', '卯', '辰', '巳', '午', '未');
        $b12 = array( 8,    9,    10,   11,   12,   1,    2,    3,    4,    5,    6,    7);

        $s9  = $y % 9;  while ($s9 <0) { $s9 = $s9 + 9; }
        $s10 = $y % 10; while ($s10<0) { $s10 =$s10 + 10; }
        $s12 = $y % 12; while ($s12<0) { $s12 =$s12 + 12; }

        $n9  = $b9[$s9];
        $n10 = $b10[$s10];
        $n12 = $b12[$s12];

        return(array('ynum9'=>$n9,    'ynum10'=>$n10,    'ynum12'=>$n12));
    }

    // Julian day number -> 月の9,10,12を求める
    function JDtoMonthStars($jd0)
    {
        $jd = (float)$jd0;
        $a = self::JDtoYearStars($jd);
        $n9 = $a['ynum9'];
        $n10= $a['ynum10'];
        $n12= $a['ynum12'];

        // ***月***
        //月を決定。24節気使う
        $a = self::JDto24Mon($jd);
        $m = $a['m']; $long = $a['long'];

        // 1月と判定されたら、年を調整する必要あり。年は2/3で判定しているから
        if (1 == $m) {
            $gday = Julian::JD2G($jd);
            if (2 == $gday['m']) {
                $n9 = $n9 + 1; if ($n9 >9) {$n9 =1;}
                $n10= $n10- 1; if ($n10<1) {$n10=10;}
                $n12= $n12- 1; if ($n12<1) {$n12=12;}
            }
        }

        // 月の九星
        $mx = intval((13 - $m) / 12); // 1月の時だけ1。それ以外は0
        $mm = $m + 12*$mx; // 1月=>13に。それ以外はそのまま。(2月=2,3月=3,...12月=12,1月=13)
        /* これでもOK
        $mod = ($n9 + 2) % 3;
        $sum_m = (2 - $mod) * 12 + $mm +2;
        $mnum9 = 9 - ($sum_m % 9);
        */
        $mod = ($n9 - 1) % 3;
        $mnum9 = $mod * 3 + 10 - $mm;
        while ($mnum9 > 9) { $mnum9 = $mnum9 - 9; }
        while ($mnum9 < 0) { $mnum9 = $mnum9 + 9; }

        // 月の12支は、そのまま使う。
        $mnum12 = $m;

        // 月の十干
        // (甲・己)年の2(寅)月,12(子)月 => 丙。順次増えていく。60ヶ月で一巡。
        $x = ($n10 -1) % 5; // (甲・己年)=0, (乙・庚年)=1, (丙・辛年)=2, (丁・壬年)=3, (戊・癸年)=4
        $mnum10 = ($x * 12 + $mm) % 10 + 1;

        // 月の六曜: 無い

        return(array('mnum9'=>$mnum9, 'mnum10'=>$mnum10, 'mnum12'=>$mnum12, 'long'=>$long, 'ynum9'=>$n9, 'ynum10'=>$n10, 'ynum12'=>$n12));
    }

    // Julian day number -> 日の9,10,12を求める
    function JDtoDayStars($jd0)
    {
        $jd = (float)$jd0;
        $a = self::JDto10kan($jd); $dnum10 = $a['num'];
        $a = self::JDto12si($jd);  $dnum12 = $a['num'];
        /*
          9星切り替えの概念は、listTurn9()、listTurn9sp() 参照
        */
        $mjd = Julian::JD2MJD($jd);
        $mjd = intval($mjd); //単純化

        $p = $mjd + 3980; // magic number = 3980
        $x = $p % 4200;

        if (0 == $x) { //特異点
            if ( ($p % 8400) == 0 ) { $dnum9 = 7; } else { $dnum9 = 3; }
        }
        else {
            //前後の切替日をさがせ!
            // 前の特異点
            $prev_sp = intval($p / 4200) * 4200;
            // 次の特異点
            $next_sp = $prev_sp + 4200;

            //直前、直後の特異点の星
            if (0 == ($prev_sp % 8400)) { $prev_sp_num9 = 7; } else { $prev_sp_num9 = 3; }
            if (0 == ($next_sp % 8400)) { $next_sp_num9 = 7; } else { $next_sp_num9 = 3; }

            //現時点は、どの区間か？+180日されるところにいるのか？+210か？
            // 直前の切り替え日をさがせ
            if (3990 <= $x) {
                $prev_change = $next_sp - 210;
                if (3 == $next_sp_num9) { //陽遁
                    $prev_change_num9 = 1; $p_m = 1;
                }
                else { //陰遁
                    $prev_change_num9 = 9; $p_m =-1;
                }
            }
            elseif ($x < 210) {
                $prev_change = $prev_sp;
                $prev_change_num9 = $prev_sp_num9;
                if (3 == $prev_sp_num9) { $p_m =-1; } else { $p_m = 1; }
            }
            else {
                // 210 <= $x < 3990 つまり、+180 の区間に居る
                $y = $x - 30; //最初の区間 210日。180にする
                $prev_change = $prev_sp + 30 + intval($y / 180) * 180;

                // 直前の切替日は何月？ -> 9星がわかる
                $a = Julian::MJD2G( ($prev_change - 3980) );
                if (1 == $a['m']) { $prev_change_num9 = 1; $p_m = 1; }
                elseif (10 <= $a['m'] && $a['m'] <= 12) { $prev_change_num9 = 1; $p_m = 1; }
                elseif ( 5 <= $a['m'] && $a['m'] <=  8) { $prev_change_num9 = 9; $p_m =-1; }
            }

            //現時点との日数差。直前の切り替えから何日たったか？
            $ddiff = $p - $prev_change;
            if ($p_m > 0) { // 陽遁
                $dnum9 = ($prev_change_num9 + $ddiff) % 9;
                if (0 == $dnum9) { $dnum9 = 9; }
            }
            else { // 陰遁
                $dnum9 = 9 - $prev_change_num9;
                $dnum9 = ($dnum9 + $ddiff) % 9;
                $dnum9 = 9 - $dnum9;
            }
        }

        return(array('dnum9'=>$dnum9, 'dnum10'=>$dnum10, 'dnum12'=>$dnum12));
    }

    // Julian day number -> 年月日の9,10,12を求める
    function JDto91012($jd0)
    {
        $jd = (float)$jd0;

        // ***年***
        /*
        $a = self::JDtoYearStars($jd);
        $n9  = $a['ynum9'];
        $n10 = $a['ynum10'];
        $n12 = $a['ynum12'];
        */

        // ***月***
        $a = self::JDtoMonthStars($jd);
        $mnum9  = $a['mnum9'];
        $mnum10 = $a['mnum10'];
        $mnum12 = $a['mnum12'];
        $long = $a['long'];
        $n9  = $a['ynum9'];
        $n10 = $a['ynum10'];
        $n12 = $a['ynum12'];

        // ***日***
        $a = self::JDtoDayStars($jd);
        $dnum9  = $a['dnum9'];
        $dnum10 = $a['dnum10'];
        $dnum12 = $a['dnum12'];

        // ***return***
        return( array('ynum9'=>$n9,    'ynum10'=>$n10,    'ynum12'=>$n12,
                      'mnum9'=>$mnum9, 'mnum10'=>$mnum10, 'mnum12'=>$mnum12,
                      'dnum9'=>$dnum9, 'dnum10'=>$dnum10, 'dnum12'=>$dnum12,
                      'long'=>$long
                      ) );
    }
    // M Julian day number -> 9,10,12を求める
    function MJDto91012($mjd)
    {
        $jd = self::MJD2JD($mjd);
        return(self::JDto91012($jd));
    }

    // JD から 月(24節気で切り替えの月)を調べる
    function JDto24Mon($jd0)
    {
        $jd = (float)$jd0;

        // 天文計算しないので、毎月1日を月の切り替えとする
        $a = Julian::JD2G($jd);
        $m = $a['m'];
        return( array($m, 0.0, 'm'=>$m, 'long'=>0.0) );

        //天文計算する場合はこちら
        // JD から、太陽黄経度λsun度を得る
        $jdg = self::toUTCJD($jd);
        $l = Sun::JD2Lambda($jdg);
        while($l < 0.0)   { $l = $l + 360.0; }
        while($l > 360.0) { $l = $l - 360.0; }
        // array('', 285, 315, 345,  15,  45,  75, 105, 135, 165, 195, 225, 255);
        // array('', 390, 420, 450, 120, 150, 180, 210, 240, 270, 300, 330, 360);
        // array('',  30,  60,  90, 120, 150, 180, 210, 240, 270, 300, 330, 360);
        $a = $l + 105;
        while($a < 0.0)   { $a = $a + 360.0; }
        while($a > 360.0) { $a = $a - 360.0; }
        $m = intval($a / 30);
        if ($m < 1) { $m = 12; }
        if ($m > 12) { $m = 1; }
        return( array($m, $l,'m'=>$m,'long'=>$l) );
    }

    // 引数月の切り替わり(24節気)、太陽の角°返す
    function m24deg($month0)
    {
        $m = (int)$month0;
        if ($m < 1 || $m > 12) { echo "ERROR: Koyomi::m24deg(m) : m<1 m>12 \n"; exit(1); }

        $d = 285 + ($m -1) * 30;
        while ($d < 0) { $d = $d + 360; }
        while ($d > 360) { $d = $d - 360; }
        return($d);
    }


    /*
     9星切り替え日 list。特異点(三碧、七赤)のみ
     magic number = 3980。MJDの初期値=220
     0==(MJD+3890)%4200 のとき、{三碧,七赤}の甲午
     0==(MJD+3890)%4200 && 0==(MJD+3890)%8400 のとき、七赤
     0==(MJD+3890)%4200 && 4200==(MJD+3890)%8400 のとき、三碧
    */
    function listTurn9sp()
    {
        $syoki = 220;
        for ($i=0; $i<20; $i++)
        {
            $mjd = $syoki + $i * 4200;
            $jd = Julian::MJD2JD($mjd);
            $a = Julian::JD2G($jd);
            $x = $mjd + 3980;
            $d = $x % 8400;
            if ($d > 0) { $dnum9 = 3; }
            else { $dnum9 = 7; }
            $kan=self::MJDto10kan($mjd);
            $si=self::MJDto12si($mjd);
            echo "mjd=$mjd jd=$jd {$a['y']} {$a['m']} {$a['d']} 9star=$dnum9 10kan={$kan['num']} 12si={$si['num']}\n";
        }
    }

    /*
      9星切り替え日 list
      基本 180日周期。しかし、
      180 * 23 = 4140日 < 4200(特異点周期)
      4200 - 4140 = 60 足りない。下図のように振り分ける

      | +180日
      +切り替え日
      | +180日
      +切り替え日
      | +180 + 30 = +210
                 この期間、余りは 3990 <= (MJD+3980)%4200
      +切り替え日(特異点 甲午 三or七)
      | +180 + 30 = +210
                 この期間、余りは (MJD+3980)%4200 < 210
      +切り替え日
      | +180日
     */
    function listTurn9()
    {
        $syoki = 220 + 4200 * 5; // MJD220=最初の特異点おきる日
        $mjd = $syoki;
        $plus = 180;
        for ($i=0; $i<50; $i++)
        {
                $dnum9 = 0;
            $jd = Julian::MJD2JD($mjd);
            $a = Julian::JD2G($jd);
            $kan=self::MJDto10kan($mjd);
            $si=self::MJDto12si($mjd);

            $k = $mjd + 3980; // magic number = 3980
            $x = $k % 4200;
            if (0 == $x) { //特異点
                if ( ($k % 8400) == 0 ) { $dnum9 = 7; } else { $dnum9 = 3; }
            }
            else {
                if (1 == $a['m']) { $dnum9 = 1; }
                elseif (10 <= $a['m'] && $a['m'] <= 12) { $dnum9 = 1; }
                elseif ( 5 <= $a['m'] && $a['m'] <=  8) { $dnum9 = 9; }
            }

            echo "mjd=$mjd jd=$jd {$a['y']} {$a['m']} {$a['d']} 9star=$dnum9 10kan={$kan['num']} 12si={$si['num']}\n";

            if (3990 == $x || 0 == $x) { $plus = 210; }
            else { $plus = 180; }

            $mjd = $mjd + $plus;
        }
    }

    // year's 土用
    function yDoyou($y0)
    {
        $y = (int)$y0;
        $ans = array();
        //$degtable = array(297,27,117,207);
        for ($i=0; $i<4; $i++)
        {
            $deg = 297 + $i * 90; while ($deg>360) {$deg = $deg - 360;}
            $mon = 1 + $i * 3;

            $s = Julian::G2JD($y, $mon, 10, 0.0) - $this->jisa/24.0;
            $e = $s + 20;
            $j = Sun::searchDegDay($deg, $s, $e) + $this->jisa/24.0;
            $g = Julian::JD2G($j);

            $ans[] = array('y'=>$g['y'],'m'=>$g['m'],'d'=>$g['d'],'h'=>$g['h'],'min'=>$g['min'],'s'=>$g['s']);
        }
        return $ans;
    }

    // 十干,十二支 => 60干支の番号を得る
    // return: 1-60
    // error: -1
    function n1012to60num($on10, $on12)
    {
        $n10 = intval($on10); $n12 = intval($on12);
        $c = -1;

        if ($n10 < 1 || $n10 > 10) { return $c; }
        $n10 = $n10 -1; //十干を 0-9に

        if (12 == $n12) { $n12 = 0; } //子は特別: 十二支numberを 0-11 に
        if ($n12 < 0 || $n12 > 12) { return $c; }

        /* 力技: 十干に0-59の番号つける=a。十二支に0-59の番号つける=b。a==bなら
        for ($i=0; $i<6; $i++) {
            for ($k=0; $k<5; $k++) {
                $a = $n10 + $i * 10;
                $b = $n12 + $k * 12;
                if ( $b == $a ) {
                    $c = $a + 1;
                    break 2;
                }
            }
        }
        */
        /* 10*$i + $n10 == 12*$k + $n12 なのだから、
           10i = 12k +n12 -n10
         */
        for ($k=4; $k>-1; $k--) {
            $x = 12 * $k + $n12 - $n10;
            if ( 0 == ($x % 10) ) {
                $i = (int)($x / 10);
                $c = 10 * $i + $n10 + 1;
                break;
            }
        }

        return $c;
    }

    // 60干支番号 -> 納音名を返す
    function n60toNatt($num)
    {
        $n = intval($num);
        if ($n < 1 || $n > 60) { return ''; }
        
        $n = $n -1;
        $a = intval($n / 2) +1;
        return self::$natt[$a];
    }

    // 十干,十二支 => 納音名
    function n1012toNatt($n10, $n12)
    {
        return  self::n60toNatt( self::n1012to60num($n10, $n12) );
    }

    //================================================
    // getter
    function getlist9() {
        $a = array();
        return( array('full'=>array_merge($a, self::$star9),
                      'sh'=>array_merge($a, self::$star9sh),
                      'vs'=>array_merge($a, self::$star9vs) ) );
    }
    function getlist10() {
        $a = array();
        return( array_merge($a, self::$kan10) );
    }
    function getlist12() {
        $a = array();
        return( array_merge($a, self::$si12) );
    }
    function getlist6() {
        $a = array();
        return( array_merge($a, self::$yo6) );
    }
    function getlist24deg() {
        $a = array();
        return( array_merge($a, self::$mlam) );
    }
    function getlistNatt() {
        $a = array();
        return( array_merge($a, self::$natt) );
    }


    //================================================
    // 旧暦の計算。複雑
    /*
      旧暦のルール。ただし、天保暦のルールでは、2033年に問題出る。天保暦のルールを少し変更。
      1. 新月を 各月の 1日
      2.「冬至」のある月 => 11月
      3. 前年旧11月-今年旧11月直前(旧10月)に、中12ヶ月あるとき -> 一つだけを閏月にする
      4. 中気がない月が 1個だけ -> その月を閏月にする
      5. 中気がない月が 2個 -> 春分のある月->2月、夏至のある月->5月、...
      6. (5)が適用できないとき、(5)でも月名が決まらないとき
         -> 直前の冬至に、最も近い 中気がない月 を、閏月にする
    */
        /*
           300 : 1/21
           330 : 2/21
      春分   0 : 3/21
            30 : 4/21
            60 : 5/21
      夏至  90 : 6/21
           120 : 7/21
           150 : 8/21
      秋分 180 : 9/21
           210 :10/21
           240 :11/21
      冬至 270 :12/21
        */
    // -----------------------------------------------------------------
    /*
    指定された年の、24節気の半分のリスト(中節)。ただし、前年の冬至含む
    JD (Local time)
     0: 西暦、前年12月
     1:  1月の中節のJD
     2:  2月......

     13: 次の年1月
    */
    // -----------------------------------------------------------------
    protected function listChuSetu($year0)
    {
        $y = (int)$year0;
        $a = array();

        //前年の冬至(JD)
        $j1 = Julian::G2JD($y-1, 12, 10, 0, 0)  - $this->jisa;
        $j2 = $j1 + 25;
        $a[0]['jd'] = Sun::searchDegDay(270, $j1, $j2) + $this->jisa;
        $a[0]['deg'] = 270;

        //今年の 300,330,0,30,....のリスト
        for ($i=0; $i<12; $i++) {
            $monx = $i + 1;
            $deg = fn_nm(300 + 30 * $i);
            $j1 = Julian::G2JD($y, $monx, 10, 0, 0)  - $this->jisa;
            $j2 = $j1 + 25;
            $a[$i+1]['jd'] = Sun::searchDegDay($deg, $j1, $j2) + $this->jisa;
            $a[$i+1]['deg'] = $deg;
        }
        // $a[13] 次の年1月
        $j1 = Julian::G2JD($y+1, 1, 10, 0, 0)  - $this->jisa;
        $j2 = $j1 + 25;
        $a[$i+1]['jd'] = Sun::searchDegDay(300, $j1, $j2) + $this->jisa;
        $a[$i+1]['deg'] = 300;
        return($a);
    }

    //
    protected function listSaku($year0)
    {
        $y = (int)$year0;
        $a = self::listChuSetu($y);
        $sd = array();
        foreach ($a as $a1) {
            $v = $a1['jd'];
            $p = Moon::findNewMoon($v - 8, $this->jisa);
            $n = Moon::findNewMoon($v + 5, $this->jisa);

            if ($p > 0) {
                $d1 = Julian::JD2G($p);
                $sd[] = Julian::G2JD($d1['y'], $d1['m'], $d1['d'], 0, 0, 0);
                //$sd[] = sprintf("%04d-%02d-%02d", $d1['y'], $d1['m'], $d1['d']);
                //$sd[] = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $d1['y'], $d1['m'], $d1['d'], $d1['h'], $d1['min'], $d1['s']);
            }
            if ($n > 0) {
                $d1 = Julian::JD2G($n);
                $sd[] = Julian::G2JD($d1['y'], $d1['m'], $d1['d'], 0, 0, 0);
                //$sd[] = sprintf("%04d-%02d-%02d", $d1['y'], $d1['m'], $d1['d']);
                //$sd[] = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $d1['y'], $d1['m'], $d1['d'], $d1['h'], $d1['min'], $d1['s']);
            }
        }
        $sd = array_unique($sd);
        sort($sd);
        //        return($sd);

        // 最初の朔日が、確実に冬至を含むように
        $n0 = $sd[0];
        $n1 = $sd[1];
        $c = $a[0]['jd'];
        $cg = Julian::JD2G($c);
        $cx = Julian::G2JD($cg['y'], $cg['m'], $cg['d'], 0, 0, 0);
        if ($n1 <= $cx) {
            array_shift($sd);
        }

        return($sd);
    }

    //旧暦の日付、六曜を返す
    function G2Q($y0, $m0, $d0, $h0=0, $min0=0, $s0=0)
    {
        $y = (int)$y0;
        $m = (int)$m0;
        $d = (float)$d0;
        $h = (float)$h0 ; $min = (float)$min0 ; $sec = (float)$s0 ;

        $list = self::listQ($y);
        $c = count($list);

        $d = Julian::G2JD($y, $m, $d, 0, 0, 0);
        for ($i = $c -1; $i >= 0; $i--) {
            if ($d < $list[$i]['sakujd']) {
                continue;
            }
            else {
                break;
            }
        }
        $qy = $list[$i]['y'];
        $qm = $list[$i]['m'];
        $s = $list[$i]['sakujd'];
        $qd = $d - $s + 1;

        /*六曜
          *旧暦*の各月 1日が、次のように決まっている
          旧2月  3    4    5    6    7
            8月  9    10   11   12   1
            友引 先負 仏滅 大安 赤口 先勝
        */
        $yo6 = ($qm + $qd + 4) % 6 +1;

        return( array('qy'=>$qy,'qm'=>$qm,'qd'=>$qd,'dnum6'=>$yo6) );
    }

    //
    function JD2Q($jd0)
    {
        $gd = Julian::JD2G((float)$jd0);
        return(self::G2Q($gd['y'],$gd['m'],$gd['d']));
    }

    //
    protected function listQ($year0)
    {
        $y = (int)$year0;

        $chu = self::listChuSetu($y); //西暦で、前年12月,1月,2月...12月,翌年1月 : 合計14個
        $chucnt = count($chu);
        $sd = self::listSaku($y);
        $sdcnt = count($sd);
        /*
          echo "中節\n";
          foreach ($chu as $v) {
          $g = Julian::JD2G($v['jd']);
          $s = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);
          echo "$s\n";
          }
          echo "朔日\n";
          foreach ($sd as $v) {
          $g = Julian::JD2G($v);
          $s = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);
          echo "$s\n";
          }
        */

        $qm1 = array();
        $gd = Julian::JD2G($sd[0]);
        $y = $gd['y'];
        $m = 11;
        //初期値
        for ($i=0; $i < $sdcnt -1; $i++) {
            $s0 = $sd[$i];
            $s1 = $sd[$i+1];

            $sakug = Julian::JD2G($s0);
            $sakugstr = sprintf("%04d-%02d-%02d", $sakug['y'], $sakug['m'], $sakug['d']);

            $qm1[$i] = array('y'=>$y, 'leap'=>0, 'm'=>$m, 'sakujd'=>$sd[$i], 'sakug'=>$sakugstr, 'chujd'=>NULL, 'chudeg'=>NULL);

            // 朔日は 00:00:00 で返ってきている。比較の時は、中節を日付のみに。
            for ($cc=0; $cc < $chucnt ; $cc++) {
                $cjd0o = $chu[$cc]['jd'];  $cd0 = $chu[$cc]['deg'];
                $cg = Julian::JD2G($cjd0o);
                $cjd0 = Julian::G2JD($cg['y'],$cg['m'],$cg['d'],0,0,0);

                if ($cjd0 == $s0) {
                    $qm1[$i]['chujd'] = $cjd0o;
                    $qm1[$i]['chudeg'] = $cd0;
                    break 1;
                }
                elseif($s0 <= $cjd0  &&  $cjd0 < $s1) {
                    $qm1[$i]['chujd'] = $cjd0o;
                    $qm1[$i]['chudeg'] = $cd0;
                    break 1;
                }
            }

            $m = $m + 1;
        }
        $i = $sdcnt -1;
        $s0 = $sd[$i];
        $cc = $chucnt -1;
        $cjd0o = $chu[$cc]['jd'];
        $cg = Julian::JD2G($cjd0o);
        $cjd0 = Julian::G2JD($cg['y'],$cg['m'],$cg['d'],0,0,0);
        if ($s0 <= $cjd0) {
            $sakug = Julian::JD2G($s0);
            $sakugstr = sprintf("%04d-%02d-%02d", $sakug['y'], $sakug['m'], $sakug['d']);
            $qm1[$i] = array('y'=>$y, 'leap'=>0, 'm'=>$m, 'sakujd'=>$s0, 'sakug'=>$sakugstr, 'chujd'=>NULL, 'chudeg'=>NULL);
            $qm1[$i]['chujd'] = $cjd0o;
            $qm1[$i]['chudeg'] = $chu[$cc]['deg'];
        }

        //--- 閏の月の数
        for ($i=10; $i < count($qm1); $i++) {
            if (270 == $qm1[$i]['chudeg']) {
                break;
            }
        }
        $sakucnt = $i + 1;

        $haveLeap = $sakucnt - 13; // =0,1,2

        //---
        // 月をrenumbering 前年12月$sd[0]は、旧の11月固定なので、skip
        $y = $gd['y'];
        $flag = 0;
        if (0==$haveLeap || 1==$haveLeap) {  //中気のない月が 0か1個
            $flag = 1;
            $mon = 11;
            $y1 = $y;
            $already = 1;
            for ($i=1; $i < count($qm1); $i++) {
                if ($already  &&  is_null($qm1[$i]['chujd'])) {
                    $qm1[$i]['leap'] =1;
                    $already = 0;
                }
                else {
                    $mon = $mon + 1;
                    if ($mon>12) { $y1 = $y1 + 1; $mon=1; }
                }
                $qm1[$i]['y']=$y1;
                $qm1[$i]['m']=$mon;
            }
        }
        elseif (2==$haveLeap) {  //中気のない月が 2個
            $qm2 = $qm1; // copy real values. dont copy pointers.
            $speqx = 0;
            $sumsol = 0;
            $winsol = 0;
            $mon=11;
            $y1 = $y;
            for ($i=0; $i < count($qm2); $i++) {
                $qm2[$i]['y'] = $y1;
                $qm2[$i]['m'] = $mon;
                $deg = $qm2[$i]['deg'];
                if (0 == $deg) { //春分2月
                    $qm2[$i]['m'] = 2;
                    $speqx = $i;
                    $mon = 2;
                }
                elseif (90 == $deg) { //夏至5月
                    $qm2[$i]['m'] = 5;
                    $sumsol = $i;
                    $mon = 5;
                }
                elseif (270 == $deg) { //冬至11月
                    $qm2[$i]['m'] = 11;
                    $winsol = $i;
                    $mon = 11;
                }
                elseif (! is_null($qm2[$k]['chujd'])) {
                    $mon++;
                    if ($mon>12) { $y1++; $mon=1; }
                }
            }

            $flag=0;
            if (3<=$speqx && $speqx<=4 && 6<=$sumsol && $sumsol<=8 && 11<=$winsol && $winsol<=13) {
                $mon=11; $y1 = $y;
                for ($i=1; $i < count($qm2); $i++) {
                    $m1 = $qm2[$i]['m'];
                    if (1==$m1 && 12==$mon) { $m1=13; }
                    if ($m1 - $mon ==0) {
                        $mon = $qm2[$i]['m'];
                        continue;
                    }
                    elseif ($m1 - $mon ==1) {
                        $mon = $qm2[$i]['m'];
                        continue;
                    }
                    else {
                        $flag=0; //このルールでは決まらない。次へ
                        break;
                    }
                }
                $flag = 1;
                $qm1 = $qm2;  // copy real values. dont copy pointers.
            }
            else { //このルールでは決まらない。次へ
                $flag=0;
            }
        }

        //上記ルールで決定しなかった->特別ルールへ
        if (!$flag) {
            $mon = 11;
            $y1 = $y;
            $already = 1;
            for ($i=1; $i < count($qm1); $i++) {
                if ($already && is_null($qm1[$i]['chujd'])) {
                    $already =0;
                    $qm1[$i]['leap'] = 1;
                    $qm1[$i]['m'] = $mon;
                    $qm1[$i]['y'] = $y1;
                }
                else {
                    $mon++;
                    if ($mon>12) { $y1 = $y1 + 1; $mon=1; }
                    $qm1[$i]['y'] = $y1;
                    $qm1[$i]['m'] = $mon;
                }
            }
        }

        for ($i=0; $i < count($qm1); $i++) {
            if (! is_null($qm1[$i]['chujd'])) {
                $g = Julian::JD2G($qm1[$i]['chujd']);
                $s = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $g['y'], $g['m'], $g['d'], $g['h'], $g['min'], $g['s']);
                $qm1[$i]['chug'] = $s;
            }
        }

        return($qm1);
    } //end of function


} // end of class


?>
