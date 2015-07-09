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
ただしかなり幅がある。きっちり夏至冬至前後数日では切り替わらない。夏至なら、5月末〜7月末までの間で切り替わる。

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
    /**
     * 時差記憶。天文計算するとき、時差が必要。他は Local Time でOK
     * 単位=日。内部の計算のために、ユリウス日にして記憶
     */
    protected $jisa; 

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
    //十二支展開 ; [子][木,火,土,金,水] , .....
    protected static $jyu2tenkai = array(
      array('寺鼠',	'野鼠',	 '木鼠',	'家鼠',	'溝鼠'),
      array('乳牛',	'耕牛',	 '水牛',	'牧牛',	'牽牛'),
      array('猛虎',	'寝虎',	 '暴虎',	'走虎',	'母虎'),
      array('狡兎',	'野兎',	 '家兎',	'月兎',	'玉兎'),
      array('下り竜',	'寝竜',	 '出世竜',	'上り竜',	'隠し竜'),
      array('長蛇',	'巻蛇',	 '王様蛇',	'怒り蛇',	'寝蛇'),
      array('競馬',	'神馬',	 '荷馬',	'兵隊馬',	'種馬'),
      array('白羊',	'病羊',	 '物言羊',	'野羊',	'毛羊'),
      array('王猿',	'赤猿',	 '山猿',	'芸猿',	'大猿'),
      array('水鳥',	'闘鳥',	 '野鳥',	'軍鳥',	'家鳥'),
      array('狂犬',	'猟犬',	 '野犬',	'猛犬',	'愛犬'),
      array('勇猪',	'遊び猪',	'病猪',	'家猪',	'荒猪')
    );

    // 何度も使うかもしれないので、一度計算した24節気は記憶しておく。
    // [year][deg]=jd
    protected $s24jdpool = array();
    protected function s24jdpool_exists($y, $deg) {
        if (array_key_exists("$y", $this->s24jdpool)  &&
            array_key_exists("$deg", $this->s24jdpool["$y"])
            )
        {
            return $this->s24jdpool["$y"]["$deg"];
        }
        return -10000;
    }
    protected function s24jdpool_set($y, $deg, $jd=-9999) {
        if (! array_key_exists("$y", $this->s24jdpool) ) {
            $this->s24jdpool["$y"] = array();
        }
        if (! array_key_exists("$deg", $this->s24jdpool["$y"]) ) {
            $this->s24jdpool["$y"]["$deg"] = $jd;
        }
    }

    /**
     *
     * @param  float $jisa0	時差(単位＝時)。ex.日本は9時間なので 9.0を与える
    */
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


    // この手の計算だと、JDに 0.5 がついてまわるので、修正ユリウス日を使うほうが扱いやすい。
    /**
     * Julian day number -> 日の十干
     *
     * @param  float $jd	ユリウス日
     * @return []	['str'=>string 十干文字, 'num'=>int 十干番号]
    */
    function JDto10kan($jd)
    {
        $mjd = Julian::JD2MJD((float)$jd);
        return( self::MJDto10kan($mjd) );
    }
    /**
     * Modified Julian day number -> 日の十干
     *
     * @param  float $mjd0	修正ユリウス日
     * @return []	['str'=>string 十干文字, 'num'=>int 十干番号]
    */
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


    /**
     * Julian day number -> 日の十二支
     *
     * @param  float $jd	ユリウス日
     * @return []	['str'=>string 十二支文字, 'num'=>int 十二支番号]
    */
    function JDto12si($jd)
    {
        $mjd = Julian::JD2MJD((float)$jd);
        return( self::MJDto12si($mjd) );
    }
    /**
     * Modified Julian day number -> 日の十二支
     *
     * @param  float $mjd0	修正ユリウス日
     * @return []	['str'=>string 十二支文字, 'num'=>int 十二支番号]
    */
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
    //未使用関数
    /**
     * Modified Julian day number -> その日の曜日
     *
     * @param  float $mjd0	修正ユリウス日
     * @return string	曜日の文字
    */
    function MJDtoW($mjd)
    {
        $tbl = array('水', '木', '金', '土', '日', '月', '火');
        $x = $mjd % 7;
        return( $tbl[$x] );
    }



    /**
     * Julian day number -> 年の9,10,12を求める
     *
     * @param  float $jd0	ユリウス日(Local time)
     * @return array	[ 'ynum9'=>int 年9星, 'ynum10'=>int 年十干, 'ynum12'=>int 年十二支 ]
    */
    function JDtoYearStars($jd0)
    {
        $jd = (float)$jd0;
        // ***年***
if (!HAVE_ASTRO_SUN):
        /* 天文計算しない場合(太陽):
            01-01 〜 02-03 一杯までは前年扱い。その分、日数(固定値34日)を引いて計算すると楽。
        */
        $x = $jd - 34;
        $ar = Julian::JD2G($x);
        $y = $ar['y'];

else: //天文計算あり
        /* 本当は2/4も24節気計算しないといけない。2/5(or2/3)が切り替えの時がある。
         * 2/5切り替えの年: 1916,1919,1920,1923,1924,1927,1928,1931,1932,1935,1936,1939,1940,1943,1944,1947,1948,1951,1952,1956,1960,1964,1968,1972,1976,1980,1984,
         * 2/3切り替えの年: 2021,2025,2029,2033,2037,2041,
        */
        $ar = Julian::JD2G($jd);
        $y = $ar['y'];
        $deg = self::$mlam[2]; // 立春2月 315d
        if (self::s24jdpool_exists("$y", "$deg") > -9999) { $t = $this->s24jdpool["$y"]["$deg"]; }
        else {
            $s = Julian::G2JD($y, 2,  1, 0.0) - $this->jisa;
            $e = $s + 10;
            $t = Sun::searchDegDay($deg, $s, $e) + $this->jisa;
            self::s24jdpool_set("$y", "$deg", $t);
        }
        if ($jd < $t) { $y = $y -1; }
endif;
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

    //
    /**
     * Julian day number -> 月の9,10,12を求める
     *
     * @param  float $jd0	ユリウス日(Local time)
     * @return array	[ 'ynum9'=>int 年9星, 'ynum10'=>int 年十干, 'ynum12'=>int 年十二支,  'mnum9'=>int 月9星, 'mnum10'=>int 月十干, 'mnum12'=>int 月十二支,  'long'=>float 太陽黄経°]
    */
    function JDtoMonthStars($jd0)
    {
        $jd = (float)$jd0;

if (!HAVE_ASTRO_SUN): // 太陽の天文計算しない場合: 毎月1日を月の切り替えとする
            $a = Julian::JD2G($jd);
            $m = $a['m'];
            return( array($m, 0.0, 'm'=>$m, 'long'=>0.0) );
endif;

        $a = self::JDtoYearStars($jd);

        $n9 = $a['ynum9'];
        $n10= $a['ynum10'];
        $n12= $a['ynum12'];

        // ***月***
        //月を決定。24節気使う
        $a = self::JDto24Mon($jd);
        $m = $a['m']; $long = $a['long'];

        // 1月と判定されたら、年を調整する必要あり。年は2/3で判定しているから
        //  -> JDtoYearStars で対応済

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

        // 月の六曜: 旧暦から計算しなくてはいけない

        return(array('mnum9'=>$mnum9, 'mnum10'=>$mnum10, 'mnum12'=>$mnum12, 'long'=>$long, 'ynum9'=>$n9, 'ynum10'=>$n10, 'ynum12'=>$n12));
    }

    /**
     * Julian day number -> 日の9,10,12を求める
     *
     * @param  float $jd0	ユリウス日(Local time)
     * @return array	[ 'dnum9'=>int 日9星, 'dnum10'=>int 日十干, 'dnum12'=>int 日十二支 ]
    */
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

    // 
    /**
     * Julian day number -> 年月日の9,10,12を求める
     *
     * @param  float $jd0	ユリウス日(Local time)
     * @return array	[ 'ynum9'=>int 年9星, 'ynum10'=>int 年十干, 'ynum12'=>int 年十二支,  'mnum9'=>int 月9星, 'mnum10'=>int 月十干, 'mnum12'=>int 月十二支,  'dnum9'=>int 日9星, 'dnum10'=>int 日十干, 'dnum12'=>int 日十二支,  'long'=>float 太陽黄経°]
    */
    function JDto91012($jd0)
    {
        $jd = (float)$jd0;

        // ***年***

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

    /**
     * Modified Julian day number -> 年月日の9,10,12を求める
     *
     * @param  float $jd0	ユリウス日
     * @return array	[ 'ynum9'=>int 年9星, 'ynum10'=>int 年十干, 'ynum12'=>int 年十二支,  'mnum9'=>int 月9星, 'mnum10'=>int 月十干, 'mnum12'=>int 月十二支,  'dnum9'=>int 日9星, 'dnum10'=>int 日十干, 'dnum12'=>int 日十二支,  'long'=>float 太陽黄経°]
    */
    function MJDto91012($mjd)
    {
        $jd = self::MJD2JD($mjd);
        return(self::JDto91012($jd));
    }



    /**
     * JD から 月(24節気で切り替えの月)を調べる
     *
     * @param  float $jd0	ユリウス日(Local time)
     * @return array	[int 月, 'm'=>int 月, 'long'=>float 太陽黄経°]
    */
    protected function JDto24Mon($jd0)
    {
        $jd = (float)$jd0;

        $g = Julian::JD2G($jd);
        $y = $g['y'];
        $m = $g['m'];
        $deg = self::$mlam[ $m ]; //指定された月の、切り替え節気の角度
        if (self::s24jdpool_exists("$y", "$deg") > -9999) { $t = $this->s24jdpool["$y"]["$deg"]; }
        else {
            // 毎月1日〜10日までの間で、切り替え日探す
            $s = Julian::G2JD($y, $m, 1, 0.0) - $this->jisa;
            $e = $s + 10;
            $t = Sun::searchDegDay($deg, $s, $e) + $this->jisa; // local time.
            self::s24jdpool_set("$y", "$deg", $t);
        }
        if ($jd < $t) { $m = $m -1; }
        if ($m < 1) { $m = 12; }
        if ($m > 12) { $m = 1; }
        return array($m, 0.0, 'm'=>$m, 'long'=>0.0);

    }


    // 引数月の切り替わり(24節気)、太陽の角°返す
    // 未使用関数
    function m24deg($month0)
    {
        $m = (int)$month0;
        if ($m < 1 || $m > 12) { echo "ERROR: Koyomi::m24deg(m) : m<1 m>12 \n"; exit(1); }

        $d = 285 + ($m -1) * 30;
        while ($d < 0) { $d = $d + 360; }
        while ($d > 360) { $d = $d - 360; }
        return($d);
    }


    //================================================
    // その年の 夏至 Summer Solstice
    // 未使用関数
    function summerSol($y0)
    {
        $y = (int)$y0;
        if (self::s24jdpool_exists($y, "90") > -9999) { return $this->s24jdpool["$y"]["90"]; }
        $s = Julian::G2JD($y, 6, 15, 0.0); // gives UTC. no problems.
        $e = $s + 15;
        $ans = Sun::searchDegDay(90, $s, $e) + $this->jisa; // return local time.
        self::s24jdpool_set($y, "90", $ans);
        return $ans;
    }
    // その年の 冬至 Winter Solstice
    // 未使用関数
    function winterSol($y0)
    {
        $y = (int)$y0;
        if (self::s24jdpool_exists($y, "90") > -9999) { return $this->s24jdpool["$y"]["90"]; }
        $s = Julian::G2JD($y, 12, 15, 0.0);
        $e = $s + 15;
        $ans = Sun::searchDegDay(270, $s, $e) + $this->jisa;
        self::s24jdpool_set($y, "270", $ans);
        return $ans;
    }



    //================================================
    /*
     9星切り替え日 list。特異点(三碧、七赤)のみ
     magic number = 3980。MJDの初期値=220(1859-06-25 三碧)
     0==(MJD+3980)%4200 のとき、{三碧,七赤}の甲午
     0==(MJD+3980)%4200 && 0==(MJD+3980)%8400 のとき、七赤
     0==(MJD+3980)%4200 && 4200==(MJD+3980)%8400 のとき、三碧
    */
    // 未使用関数
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
    // 未使用関数
    function listTurn9()
    {
        $syoki = 220 + 4200 * 5; // MJD220=最初の特異点おきる日 1859-06-25 三碧
        $mjd = $syoki;
        $plus = 180;
        // 切替日を50個出力
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

            echo "mjd=$mjd jd=$jd {$a['y']} {$a['m']} {$a['d']} 9star={$dnum9} 10kan={$kan['num']} 12si={$si['num']}<br>\n";

            if (3990 == $x || 0 == $x) { $plus = 210; }
            else { $plus = 180; }

            $mjd = $mjd + $plus;
        }
    }

    /**
     * 指定時に近い九星切替日を得る
     *
     * @param  float $jd	ユリウス日(Local time でよい)
     * @return array	[float 前の切り替えJD, int 前の切り替え九星,  float 次の切り替えJD, int 次の切り替え九星]
    */
    // 未使用関数
    function getNearTurn9($jd)
    {
        $jd = floatVal( $jd );
        $mjd = intVal( Julian::JD2MJD($jd) );

        $p = $mjd -220; // magic number  3980 + 220 = 4200  MJD220=1859-06-25 三碧
        $x = $p % 4200;

        //特異点。1日ずらそう。
/*      if (0 == $x) {
            $p = $p + 1;
            $x = $p % 4200;
        }
*/
            //前後の切替日をさがせ!
            // 前の特異点MJD
            $prev_sp = intval($p / 4200) * 4200 +220;
            // 次の特異点MJD
            $next_sp = $prev_sp + 4200;

            //直前、直後の特異点の星
            if (0 == (($prev_sp -220) % 8400)) { $prev_sp_num9 = 3; } else { $prev_sp_num9 = 7; }
            if (0 == (($next_sp -220) % 8400)) { $next_sp_num9 = 3; } else { $next_sp_num9 = 7; }

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
                $next_change = $next_sp;
                $next_change_num9 = $next_sp_num9;
            }
            elseif ($x < 210) {
                $prev_change = $prev_sp;
                $prev_change_num9 = $prev_sp_num9;
                if (3 == $prev_sp_num9) { $p_m =-1; } else { $p_m = 1; }

                $next_change = $prev_sp + 210;
                $next_change_num9  =  ( $p_m > 0 )  ?  9  :  1;
            }
            else {
                // 210 <= $x < 3990 つまり、+180 の区間に居る
                $y = $x - 30; //最初の区間 210日。180にする
                $prev_change = $prev_sp + 30 + intval($y / 180) * 180;

                // 直前の切替日は何月？ -> 9星がわかる
                $a = Julian::MJD2G( $prev_change );
                if (1 == $a['m']) { $prev_change_num9 = 1; $p_m = 1; }
                elseif (10 <= $a['m'] && $a['m'] <= 12) { $prev_change_num9 = 1; $p_m = 1; }
                elseif ( 5 <= $a['m'] && $a['m'] <=  8) { $prev_change_num9 = 9; $p_m =-1; }

                $next_change = $prev_change + 180;
                $next_change_num9  =  ( $p_m > 0 )  ?  9  :  1;
            }
        $jdprev = Julian::MJD2JD($prev_change);
        $jdnext = Julian::MJD2JD($next_change);
        return(array ( $jdprev , $prev_change_num9,
                       $jdnext , $next_change_num9 )   );
    }

    //================================================
    /**
     * 指定された年の、土用の日時を返す
     *
     * @param  int $y0	西暦年
     * @param  float $d0        西暦日
     * @param  float $h0        西暦時
     * @param  float $min0      西暦分
     * @param  float $s0        西暦秒
     * @return [][]  	[]['y'=>int 年,'m'=>int 月,'d'=>int 日, 'h'=>int 時, 'min'=>int 分, 's'=>float 秒]
     *               	0: 春の土用
     *               	1: 夏の土用
     *               	2: 秋の土用
     *               	3: 冬の土用
    */
    function yDoyou($y0)
    {
        $y = (int)$y0;
        $ans = array();
        //$degtable = array(297,27,117,207);
        for ($i=0; $i<4; $i++)
        {
            $deg = 297 + $i * 90; while ($deg>360) {$deg = $deg - 360;}
            $mon = 1 + $i * 3;

            $s = Julian::G2JD($y, $mon, 10, 0.0) - $this->jisa;
            $e = $s + 20;
            if (self::s24jdpool_exists("$y", "$deg") > -9999) {
                $j = $this->s24jdpool["$y"]["$deg"];
            } else {
                $j = Sun::searchDegDay($deg, $s, $e) + $this->jisa;
                self::s24jdpool_set("$y", "$deg", $j);
            }
            $g = Julian::JD2G($j);

            $ans[] = array('y'=>$g['y'],'m'=>$g['m'],'d'=>$g['d'],'h'=>$g['h'],'min'=>$g['min'],'s'=>$g['s']);

        }
        return $ans;
    }


    //================================================
    /**
     * 指定された年の24節気計算
     *
     * @param  int $y0	年
     * @return [] 	"角度"=>ユリウス日(Local time) の配列
    */
    //未使用関数
    function y24sekki($y0)
    {
        $y = (int)$y0;
        for ($i=1; $i<=12; $i++)
        {
            $s = Julian::G2JD($y, $i, 1, 0.0) - $this->jisa;
            $e = $s + 10;

            $deg = self::$mlam[$i];
            if (self::s24jdpool_exists("$y", "$deg") > -9999) {
                $j = $this->s24jdpool["$y"]["$deg"];
            } else {
                $j = Sun::searchDegDay($deg, $s, $e) + $this->jisa;
                self::s24jdpool_set("$y", "$deg", $j);
            }

            $deg = self::$mchu[$i];
            if (self::s24jdpool_exists("$y", "$deg") > -9999) {
                $j = $this->s24jdpool["$y"]["$deg"];
            } else {
                $j = Sun::searchDegDay($deg, $s+15, $e+15) + $this->jisa;
                self::s24jdpool_set("$y", "$deg", $j);
            }
        }
        return( $this->s24jdpool["$y"] );
    }

    /**
     * 指定された年・月にある、ふたつの24節気計算
     *
     * @param  int $y0	年
     * @param  int $m0	月
     * @return [] 	"角度"=>ユリウス日(Local time) の配列
    */
    //未使用関数
    function ym24sekki($y0, $m0)
    {
        $y = (int)$y0;
        $m = (int)$m0;
        if ($m < 1) { $m=1; }
        if ($m > 12) {$m=12;}

        $a = array();

            $s = Julian::G2JD($y, $m, 1, 0.0) - $this->jisa;
            $e = $s + 10;

            $deg = self::$mlam[$m];
            if (self::s24jdpool_exists("$y", "$deg") > -9999) {
                $j = $this->s24jdpool["$y"]["$deg"];
            } else {
                $j = Sun::searchDegDay($deg, $s, $e) + $this->jisa;
                self::s24jdpool_set("$y", "$deg", $j);
            }
            $a["$deg"] = $j;

            $deg = self::$mchu[$m];
            if (self::s24jdpool_exists("$y", "$deg") > -9999) {
                $j = $this->s24jdpool["$y"]["$deg"];
            } else {
                $j = Sun::searchDegDay($deg, $s, $e) + $this->jisa;
                self::s24jdpool_set("$y", "$deg", $j);
            }
            $a["$deg"] = $j;

        return $a;
    }




    //================================================
    /**
     * 十干,十二支 => 60干支の番号を得る
     *
     * @param  int $on10	十干(1-10)
     * @param  int $on12	十二支(1-12)
     * @return int 	1-60の番号。エラーのとき -1
    */
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

    /**
     * 60干支番号 -> 納音名を返す
     *
     * @param  int $num	60干支番号(1-60)
     * @return string 	納音名。エラーのとき ''
    */
    function n60toNatt($num)
    {
        $n = intval($num);
        if ($n < 1 || $n > 60) { return ''; }
        
        $n = $n -1;
        $a = intval($n / 2) +1;
        return self::$natt[$a];
    }

    /**
     * 十干,十二支 => 納音名
     *
     * @param  int $n10	十干(1-10)
     * @param  int $n12	十二支(1-12)
     * @return string 	納音名。エラーのとき ''
    */
    function n1012toNatt($n10, $n12)
    {
        return  self::n60toNatt( self::n1012to60num($n10, $n12) );
    }


    /**
     * 十干,十二支 => 十二支の展開名
     *
     * @param  int $n10	十干(1-10)
     * @param  int $n12	十二支(1-12)
     * @return string 	納音名。エラーのとき ''
    */
    function n1912Tenkai($n10, $n12)
    {
      $n10 = intval($n10) -1;
      $n12 = intval($n12);
      if ($n12<0 || $n12>12) { return '';}
      if ($n10<0 || $n10>9) { return '';}
      if (12 == $n12) { $n12=0; } //子
      $g = intval($n10 / 2);
      return self::$jyu2tenkai[$n12][$g];
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
    function getlist12tenkai() {
        return(self::$jyu2tenkai);
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

    /**
     * 指定された日の、旧暦の日付、六曜を返す
     *
     * @param  int $y0  西暦年
     * @param  int $m0  西暦月
     * @param  float $d0        西暦日
     * @param  float $h0        西暦時
     * @param  float $min0      西暦分
     * @param  float $s0        西暦秒
     * @return array	['qy'=>int 旧暦年,'qm'=>int 旧暦月,'qd'=>int 旧暦日, 'dnum6'=>旧暦日の六曜]
    */
    function G2Q($y0, $m0, $d0, $h0=0, $min0=0, $s0=0)
    {
if (! HAVE_ASTRO_MOON ):
        // 天文計算(月)しない場合
        return NULL;
endif;
        $y = (int)$y0;
        $m = (int)$m0;
        $d = (float)$d0;
        $h = (float)$h0 ; $min = (float)$min0 ; $sec = (float)$s0 ;

        $list = self::listQ($y); // 12月1月2月....12月1月

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


    /**
     * 指定された日の、旧暦の日付、六曜を返す
     *
     * @param  float $jd0        ユリウス日
     * @return array	['qy'=>int 旧暦年,'qm'=>int 旧暦月,'qd'=>int 旧暦日, 'dnum6'=>旧暦日の六曜]
    */
    function JD2Q($jd0)
    {
        $gd = Julian::JD2G((float)$jd0);
        return(self::G2Q($gd['y'],$gd['m'],$gd['d']));
    }



    /**
     * 指定された年の、24節気の半分のリスト(中節)。ただし、前年の冬至含む
     *
     * @access protected
     * @param  int  $year0	西暦
     * @return array	[]['jd'=>ユリウス日(Local time), 'deg'=>太陽の黄経°]
     *              	 0: 西暦、前年12月の中節のJD
     *              	 1:  1月の中節のJD
     *              	 2:  2月......
     *               	         :
     *              	13: 次の年1月
    */
    protected function listChuSetu($year0)
    {
        $y = (int)$year0;
        $a = array();

        //前年の冬至(JD)
        $py = $y -1;
        $j1 = Julian::G2JD($y-1, 12, 10, 0, 0)  - $this->jisa;
        $j2 = $j1 + 25;
        $a[0]['deg'] = 270;
        if (self::s24jdpool_exists("$py", "270") > -9999) {
            $a[0]['jd'] = $this->s24jdpool["$py"]["270"];
        } else {
            $a[0]['jd'] = Sun::searchDegDay(270, $j1, $j2) + $this->jisa;
            self::s24jdpool_set("$py", "270", $a[0]['jd']);
        }

        //今年の 300,330,0,30,....のリスト
        for ($i=0; $i<12; $i++) {
            $monx = $i + 1;
            $deg = fn_nm(300 + 30 * $i);
            $j1 = Julian::G2JD($y, $monx, 10, 0, 0)  - $this->jisa;
            $j2 = $j1 + 25;
            $a[$i+1]['deg'] = $deg;
            if (self::s24jdpool_exists("$y", "$deg") > -9999) {
                $a[$i+1]['jd'] = $this->s24jdpool["$y"]["$deg"];
            } else {
                $a[$i+1]['jd'] = Sun::searchDegDay($deg, $j1, $j2) + $this->jisa;
                $this->s24jdpool["$y"]["$deg"] = $a[$i+1]['jd'];
            }
        }
        // $a[13] 次の年1月
        $j1 = Julian::G2JD($y+1, 1, 10, 0, 0)  - $this->jisa;
        $j2 = $j1 + 25;
        $a[$i+1]['deg'] = 300;
        if (self::s24jdpool_exists("$y", "300") > -9999) {
            $a[$i+1]['jd'] = $this->s24jdpool["$y"]["300"];
        } else {
            $a[$i+1]['jd'] = Sun::searchDegDay(300, $j1, $j2) + $this->jisa;
            $this->s24jdpool["$y"]["300"] = $a[$i+1]['jd'];
        }
        return($a);
    }


    /**
     * 指定された年の、中節付近の新月のリスト。ただし、前年の冬至含む
     *
     * @access protected
     * @param  int  $year0	西暦
     * @return float[]	[ユリウス日(Local time)]
     *              	 0: 西暦、前年12月の中節付近の新月のJD
     *               	         :
     *              	 n: 次の年1月
    */
    protected function listSaku($year0)
    {
        $y = (int)$year0;
        $a = self::listChuSetu($y);

        $sd = array();
        foreach ($a as $a1) {
            $v = $a1['jd'] - $this->jisa;

            // moon.php
            $data = Moon::findNewMoon($v - 20, $v + 30);
            foreach ($data as $dnm) {
                $sd[] = $dnm + $this->jisa;
            }
        }
        $sd = array_unique($sd);
        sort($sd);

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


    /**
     * 指定された年の、旧暦のリスト。ただし、前年の冬至含む
     *
     * @access protected
     * @param  int  $year0	西暦
     * @return [][] 	[][ 'y'=>int 年, 'leap'=>int 閏月なら1, 'm'=>int 月, 'sakujd'=>float 朔日JD, 'sakug'=>string 朔日を"%04d-%02d-%02d"表記, 'chujd'=>float 中気のJD、なければNULL, 'chudeg'=>float 中気の黄経、なければNULL ]
     *              	 0: 西暦、前年12月の冬至付近の旧暦情報
     *               	         :
     *              	 n: 次の年1月
    */
    protected function listQ($year0)
    {
if (! HAVE_ASTRO_MOON ):
        // 天文計算(月)しない場合
        return NULL;
endif;
        $y = (int)$year0;

        $chu = self::listChuSetu($y); //西暦で、前年12月,1月,2月...12月,翌年1月 : 合計14個
        $chucnt = count($chu);
        $sd = self::listSaku($y);
        $sdcnt = count($sd);

        $qm1 = array();
        $gd = Julian::JD2G($sd[0]);
        $y = $gd['y'];
        //初期値
        $m = 11;
        // 比較の時は日付のみに。
        for ($i=0; $i < $sdcnt -1; $i++) {
            $s0 = $sd[$i];
            $s1 = $sd[$i+1];

            $t = Julian::JD2G($s0);
            $s0 = Julian::G2JD($t['y'],$t['m'],$t['d'],0,0,0);
            //if (23==$t['h']) { $s0++; }
            $t = Julian::JD2G($s1);
            $s1 = Julian::G2JD($t['y'],$t['m'],$t['d'],0,0,0);
            //if (23==$t['h']) { $s1++; }

            $sakug = Julian::JD2G($s0);
            $sakugstr = sprintf("%04d-%02d-%02d", $sakug['y'], $sakug['m'], $sakug['d']);

            $qm1[$i] = array('y'=>$y, 'leap'=>0, 'm'=>$m, 'sakujd'=>$s0, 'sakug'=>$sakugstr, 'chujd'=>NULL, 'chudeg'=>NULL);

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
            $t = Julian::JD2G($s0);
            $s0 = Julian::G2JD($t['y'],$t['m'],$t['d'],0,0,0);

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
    } //end of function listQ


} // end of class


?>
