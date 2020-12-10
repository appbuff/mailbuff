<?php
function addemailcategory(){

  $free_account = array('outlook.com','outlook.com.br','outlook.in','hotmail.be','hotmail.co.il','hotmail.co.uk','hotmail.com','hotmail.com.ar','hotmail.com.br','hotmail.com.mx','hotmail.de','hotmail.es','hotmail.fr','hotmail.it','hotmail.kz',
  'hotmail.work','aol.com','aol.it','mail.anhthu.org','mail.az','mail.bccto.me','mail.be','mail.bulgaria.com','mail.co.za','mail.com','mail.crowdpress.it','mail.defaultdomain.ml','mail.ee','mail.fettometern.com','mail.freetown.com','mail.gr',
  'mail.hanungofficial.club','mail.hitthebeach.com','mail.jpgames.net','mail.kmsp.com','mail.libivan.com','mail.md','mail.mezimages.net','mail.mixhd.xyz','mail.mnisjk.com','mail.nu','mail.org.uk','mail.partskyline.com','mail.pt','mail.r-o-o-t.com',
  'mail.ru','mail.salu.net','mail.sisna.com','mail.spaceports.com','mail.stars19.xyz','mail.twfaka.com','mail.vasarhely.hu','mail.wtf','mail.wvwvw.tech','mail.by','live.be','live.co.uk','live.com','live.com.ar','live.com.au',
  'live.com.mx','live.de','live.fr','live.it','live.nl','gmai.com','gmail.ax','gmail.com','gmail.gr.com','gmail.zalvisual.us','qq.com','google.com','msn.com','yaho.com','yahoo.ca','yahoo.co.id','yahoo.co.in','yahoo.co.jp','yahoo.co.kr',
  'yahoo.co.nz','yahoo.co.uk','yahoo.com','yahoo.com.ar','yahoo.com.au','yahoo.com.br','yahoo.com.cn','yahoo.com.hk','yahoo.com.mx','yahoo.com.ph','yahoo.com.ru','yahoo.com.tw', 'yahoo.com.sg','yahoo.de','yahoo.dk','yahoo.es','yahoo.fr','yahoo.ie','yahoo.in',
  'yahoo.it','yahoo.jp','yahoo.ru','yahoo.se');

  $role_account = array('info','support','account','contact','sales','orders','help','inquery','enquery');

  $email_category_sql = "INSERT INTO email_category (name,e_type,catch_all_check,user_id) VALUES";
  $coma_check = false;
  foreach ($role_account as $value) {
    if(!$coma_check){
      $email_category_sql .=  ' ("'.$value.'","Role Account","1","all")';
      $coma_check = true;
    }else{
      $email_category_sql .=  ', ("'.$value.'","Role Account","1","all")';
    }
  }
  foreach ($free_account as $value) {
    $email_category_sql .=  ', ("'.$value.'","Free Account","0","all")';
  }
  foreach ($disposable_account as $value) {
    $email_category_sql .= ', ("'.$value.'","Disposable Account","1","all")';
  }
  return $email_category_sql;

}
?>