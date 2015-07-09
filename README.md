// -*- coding: utf-8 -*-

指定されたユリウス日の、九星、十干、十二支、納音、(旧暦日、六曜)得るプログラム  
使い方は、  
　test.php  
参考。  

太陽、月の天文計算(角度λ)をする部分は空っぽです。  
天文計算をする場合は  
　sun.php  
　moon.php  
を埋めるようにします。  
またcommon.php内のHAVE_ASTRO_SUN HAVE_ASTRO_MOONをtrueにします。  
define('HAVE_ASTRO_SUN', true);  
天文計算ルーチンがないので、月替りの日時、土用の日時、旧暦および旧暦をもとにしている六曜は出ません。また月替わりは1日です。  



- koyomi.php　　　本体  
- common.php　　　共通の定義  
- common_fn.php　　共通の関数  
- julian.php　　ユリウス日計算。PHPが持っている関数は使いにくいので。  
- sun.php　　 　太陽の黄経計算  
- moon.php　　　月の計算  


  
ざっくり作ったものなので  
　catch ... throw  
はしてません。エラー吐いて終わり。  


  
\*\* 月替わり(24節気)、朔日(新月)の計算について  

月替わり(24節気)、朔日(新月)の計算は、満足できるものがありませんでした。(2015-06-06)
　天文計算ライブラリ  
　　libnova 0.15.0 ( http://libnova.sourceforge.net/ )  
　　libastro (xephem-3.7.6 http://www.xephem.com 付属のもの)  
　　を試しましたが、誤差が大きすぎて使えませんでした。  
　DE405、DE430などJPLが出しているデータとサンプルでは、到底何をどうすれば良いか分かりませんでした。天文計算は全く知りませんので。  
　その他個人がwebサイトで示しているものは、ライセンスが不明なので使えませんでした。  

追記(2015-06-29):  
　よさそうなツールがありました。  
　　http://moshier.net/  
　にある、aa-56.zip です。国立天文台の発表の数値に近いものがでます。ソースからmakeすると aa conjunct コマンドができますが、これらが今回の使用目的にぴったりで、また使いやすいです。  



きっちり合わせたい・天文計算したくないなら、天象学会の「萬年暦」や「理科年表」に乗っている値を、テーブルで持たすのが手っ取り早いかも。  

  
テーブルにする場合、  
月替わり、24節気の計算は  
　Koyomi::JDto24Mon()  
土用は  
　Koyomi::yDoyou()  
旧暦六曜は  
　Koyomi::G2Q()  
です。これらを変更すればよいでしょう。  
またテーブルに値を持たせるなら  
　Koyomi::listChuSetu()  
　Koyomi::listSaku()  
　Koyomi::listQ()  
は不要です。削除して結構です。  


License: GPL2  
https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html  
