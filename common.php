<?php
//-*- coding: utf-8 -*-
if (! defined('__MYDEFS__') ) {
define("__MYDEFS__", 1);
define("C_Rads", M_PI / 180.0);
define("C_Degs", 180.0 / M_PI);
define('Hantei1', 0.000001);
define('HAVE_ASTROCALC', false);
}
/* HAVE_ASTROCALC について
 * moon.php、sun.phpで、天文計算ルーチンを組み込んだ場合、「true」にする。
 * 「false」にすると、天文計算無しになり、年の切り替わりは「2/4 00:00」固定、月の切り替わりは「1日 00:00」固定になる。
 */

require_once('common_fn.php');
require_once('julian.php');
require_once('koyomi.php');
require_once('moon.php');
require_once('sun.php');
?>
