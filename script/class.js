class mail_template{
  replace_word = ''
  template_html = "<button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{[REPLACE]=\"\"}'>クリア</button><textarea type='memo' class='form-control' id='[REPLACE]' rows=\"10\" v-model='[REPLACE]'></textarea><div class='row mb-3 mt-2'>  <div class='col-12'>    定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)  </div></div><div class='row mb-3'>  <div class='col-12'>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<購入者名>\"}' style='width:70px;min-width:50px;'>購入者名</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<注文内容>\"}' style='width:70px;min-width:50px;'>注文内容</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<購入者情報>\"}' style='width:70px;min-width:50px;'>購入者情報</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<届け先情報>\"}' style='width:70px;min-width:50px;'>届け先情報</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<問合担当者>\"}' style='width:70px;min-width:50px;'>問合担当者</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<問合せ受付TEL>\"}' style='width:70px;min-width:50px;'>問合TEL</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<問合せ受付MAIL>\"}' style='width:70px;min-width:50px;'>問合MAIL</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<自社名>\"}' style='width:70px;min-width:50px;'>自社名</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<代表者>\"}' style='width:70px;min-width:50px;'>代表者</button>    <button type='button' class='btn btn-info m-2' @click='()=>{[REPLACE]=[REPLACE]+\"<自社住所>\"}' style='width:70px;min-width:50px;'>自社住所</button>  </div></div><small>メールサンプル</small><div class='p-2' style='white-space: pre-wrap;border:1px solid black;' v-text='[REPLACE]_sample'></div>"
  constructor(word){
    this.replace_word = word
  }

  html_auto(){
    return this.template_html.replace(/\[REPLACE\]/g,this.replace_word)
  }
}