<?php
  require "php_header_admin.php";
  $token = csrf_create();
  if(empty($_GET["key"])){
    echo "参照用のURLが異なります。";
    exit();
  }
  $user_hash = $_GET["key"] ;
  $key_user = rot13decrypt2($user_hash);
  require_once "auth.php";  //次のリリースで有効にする
  //$_SESSION["user_id"] = rot13decrypt2($user_hash);
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_admin.php" 
    ?>
    <style>
      .btn{
        min-width: 30px;
      }
      .common_main{
        padding-top:55px;
      }
    </style>
    <TITLE>受注管理</TITLE>
</head>
<BODY>
  <?php include "header_tag_admin.php"  ?>
  <div id='app'>
  <MAIN class='container common_main'>
    <div class='row mb-2 pt-3 ps-3'>
      <div class='col-12 mb-0'>
        <div class="btn-group" role="group" aria-label="Basic outlined example" >
          <input type='radio' class='btn-check' name='mode' value='未完了' autocomplete='off' v-model='mode' id='未完了' >
				  <label class='btn btn-outline-success ' for='未完了' style='border-radius:0;' >未完了</label>
          <input type='radio' class='btn-check' name='mode' value='未受付' autocomplete='off' v-model='mode' id='未受付' >
				  <label class='btn btn-outline-success ' for='未受付' style='border-radius:0;' >未受付</label>
          <input type='radio' class='btn-check' name='mode' value='未入金' autocomplete='off' v-model='mode' id='未入金' >
				  <label class='btn btn-outline-success ' for='未入金' style='border-radius:0;' >未入金</label>
          <input type='radio' class='btn-check' name='mode' value='未発送' autocomplete='off' v-model='mode' id='未発送' >
				  <label class='btn btn-outline-success ' for='未発送' style='border-radius:0;' >未発送</label>
				  <input type='radio' class='btn-check' name='mode' value='完了' autocomplete='off' v-model='mode' id='全件' >
				  <label class='btn btn-outline-success ' for='全件' style='border-radius:0;' >全件</label>
        </div>
      </div>
    </div>
    <div class='row pb-3 pt-3'>
      <template v-for='(list,index) in orderlist_hd' :key='list.orderNO'>
        <div v-if='list.完了FLG.includes(mode)' class='col-xl-6 col-md-6 col-12'><!--外枠-->
          <div class='container-fluid'>
            <div class='row pb-1'>
            </div>
            <div class='row'>
              <div class='col-12'><!--詳細-->
                <div class="accordion" :id="`accordion_${index}`">
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button" style='font-size:11px;font-weight:800;' type="button" data-bs-toggle="collapse" :data-bs-target="`#collapseOne_${index}`" aria-expanded="true" aria-controls="collapseOne">
                        <div style="width: 100%;">
                          <div style="width: 100%;">注文日[{{String(list.juchuu_date).substring(0,10)}}] 注文者：{{list.name}}  ￥{{Number(list.税込総額).toLocaleString()}}</div>
                          <div style="width: 100%;">
                            受付NO:[{{list.orderNO}}] 　
                            <template v-if='list.cancel===null'>
                              <template v-if='chk_recept'><template v-if='list.オーダー受付==="済"'><span style='color:blue;'>受付{{list.オーダー受付}}</span></template><template v-else ><span style='color:red;'>{{list.オーダー受付}}受付</span></template> 　</template>
                              <template v-if='chk_paid'><template v-if='list.入金==="済"'><span style='color:blue;'>入金{{list.入金}}</span></template><template v-else ><span style='color:red;'>{{list.入金}}入金</span></template> 　</template>
                              <template v-if='chk_sent'><template v-if='list.発送==="済"'><span style='color:blue;'>発送{{list.発送}}</span></template><template v-else ><span style='color:red;'>{{list.発送}}発送</span></template> </template>
                            </template>
                            <template v-else>
                              キャンセル済み
                            </template>
                          </div>
                          <div style="width: 100%;">購入のきっかけ：{{list.buy_trigger}}</div>
                        </div>
                      </button>
                    </h2>
                    <div :id="`collapseOne_${index}`" class="accordion-collapse collapse" :data-bs-parent="`#accordion_${index}`">
                      <div class="accordion-body">
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <button type='button' class='btn btn-success'  @click='set_qa_fmB(index)'>メールを送る</button>
                          </div>
                        </div>

                        <div v-if='list.cancel===null' class='d-flex' style="position: relative;">
                          <div v-if='chk_recept' class='me-3'>
                            <p>受付</p>
                            <input type='radio' class='btn-check' :name='`statusU_${index}`' value="未" autocomplete='off' :id='`showU_${index}`' v-model='list.オーダー受付' @change='set_order_sts(list.orderNO,"first_answer",0,index)'>
				                    <label class='btn btn-outline-danger ' :for='`showU_${index}`' style='border-radius:0;'>未</label>
				                    <input type='radio' class='btn-check' :name='`statusU_${index}`' value="済" autocomplete='off' :id='`stopU_${index}`' v-model='list.オーダー受付' @change='set_order_sts(list.orderNO,"first_answer",1,index)'>
				                    <label class='btn btn-outline-primary ' :for='`stopU_${index}`' style='border-radius:0;'>済</label>
                          </div>
                          <div v-if='chk_paid' class='me-3'>
                            <p>入金</p>
                            <input type='radio' class='btn-check' :name='`status_${index}`' value="未" autocomplete='off' :id='`show_${index}`' v-model='list.入金' @change='set_order_sts(list.orderNO,"payment",0,index)'>
				                    <label class='btn btn-outline-danger ' :for='`show_${index}`' style='border-radius:0;'>未</label>
				                    <input type='radio' class='btn-check' :name='`status_${index}`' value="済" autocomplete='off' :id='`stop_${index}`' v-model='list.入金' @change='set_order_sts(list.orderNO,"payment",1,index)'>
				                    <label class='btn btn-outline-primary ' :for='`stop_${index}`' style='border-radius:0;'>済</label>
                          </div>
                          <div v-if='chk_sent'>
                            <p>発送</p>
                            <input type='radio' class='btn-check' :name='`statusH_${index}`' value="未" autocomplete='off' :id='`showH_${index}`' v-model='list.発送' @change='set_order_sts(list.orderNO,"sent",0,index)'>
				                    <label class='btn btn-outline-danger ' :for='`showH_${index}`' style='border-radius:0;'>未</label>
				                    <input type='radio' class='btn-check' :name='`statusH_${index}`' value="済" autocomplete='off' :id='`stopH_${index}`' v-model='list.発送' @change='set_order_sts(list.orderNO,"sent",1,index)'>
				                    <label class='btn btn-outline-primary ' :for='`stopH_${index}`' style='border-radius:0;'>済</label>
                          </div>
                          <div style='position: absolute;top:10px;right:5px'>
                            <a :href="`pdf_receipt.php?hash=<?php echo $user_hash;?>&val=${list.orderNO}&tp=0`" class='btn btn-primary mt-3' target="_blank" rel="noopener noreferrer">納品書印刷</a>
                          </div>
                        </div>
                        <table class='table table-sm table-bordered caption-top'>
                          <caption>ご注文内容</caption>
                          <thead>
                            <tr>
                              <th>商品名</th>
                              <th class='text-center'>単価</th>
                              <th class='text-center'>注文数</th>
                              <th class='text-center'>総額</th>
                            </tr>
                          </thead>
                          <!--<tbody v-for='(list2,index2) in orderlist_bd' :key='list2.orderNO+list2.shouhinCD'>-->
                          <tbody>
                            <template v-for='(list2,index2) in orderlist_bd' :key='list2.SEQ'>
                              <template v-if='list.orderNO===list2.orderNO' >
                                <tr class="align-bottom">
                                  <td>
                                    {{list2.shouhinNM}}
                                  </td>
                                  <td class='text-end pe-2'>{{(Number(list2.tanka)).toLocaleString()}}</td>
                                  <td class='text-center'>
                                    {{list2.su}}
                                  </td>
                                  <td class='text-end pe-2'>{{(Number(list2.goukeitanka)+Number(list2.zei)).toLocaleString()}}</td>
                                </tr>
                                <tr v-if='list2.zei==="0.00"'>
                                  <td colspan="4">備考:{{list2.bikou}}</td>
                                </tr>
                              </template>
                            </template>
                            <tr>
                              <td colspan="4" class='p-2 text-center'>
                                <button type='button' class='btn btn-primary' @click='set_select_orderNO(list.orderNO)' data-bs-toggle="modal" data-bs-target="#exampleModal3" >商品追加</button>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td></td>
                              <td colspan="2">税込合計金額</td>
                              <td class='text-end pe-2'>{{Number(list.税込総額).toLocaleString()}}</td>
                            </tr>
                          </tfoot>
                        </table>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label :for='`post_corp${index}`' class="form-label">配送業者</label>
                            <input type='text' v-model='list.post_corp' class='form-control' :id='`post_corp${index}`' @change='set_order_sts(list.orderNO,"post_corp",list.post_corp,index)'> 
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-4 col-8'>
                            <label :for='`postage${index}`' class="form-label">送料(税込)</label>
                            <input type='text' v-model='list.postage' class='form-control' :id='`postage${index}`' @change='set_order_sts(list.orderNO,"postage",list.postage,index)'> 
                          </div>
                          <div class='col-md-2 col-4'>
                            <label :for='`postage${index}`' class="form-label">税率</label>
                            <select v-model='list.postage_zeikbn' class='form-select' :id='`postage_zeikbn${index}`' @change='set_order_sts(list.orderNO,"postage_zeikbn",list.postage_zeikbn,index)'> 
                              <option value=1101>10%</option>
                              <option value=0>非課税</option>
                            </select>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label :for='`postage_url_${index}`' class="form-label">配送状況確認URL</label>
                            <input type='text' v-model='list.postage_url' class='form-control' :id='`postage_url_${index}`' @change='set_order_sts(list.orderNO,"postage_url",list.postage_url,index)'> 
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label :for='`postage_no_${index}`' class="form-label">配送状況確認用の番号</label>
                            <input type='text' v-model='list.postage_no' class='form-control' :id='`postage_no_${index}`' @change='set_order_sts(list.orderNO,"postage_no",list.postage_no,index)'> 
                          </div>
                        </div>
                        <p>【注文者要望】</p>
                        <p>{{list.bikou}}</p>
                        <hr>
                        <p>【注文者情報】</p>
                        <p>※[<i class="bi bi-pencil-square"></i>] を押すと修正できるようになります。</p>
                        <div class='row mb-3'>
                          <div class='col-12'>
                            <label :for='`od_atena${index}`' class="form-label">お名前・宛名</label>
                            <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_atena${index}`)'>COPY</button>
                            <div class="input-group">
                              <button type='button' class='btn btn-outline-secondary' @click='unlock(`od_atena${index}`,`p_atena${index}`)'><i class="bi bi-pencil-square"></i></button>
                              <input type='text' v-model='list.name' class='form-control' :id='`od_atena${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"name",list.name,index)' disabled readonly style='display:none;'>
                              <p style='margin-left:8px;font-size:larger;' :id='`p_atena${index}`'>{{list.name}}</p>
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label :for='`od_yubin${index}`' class="form-label">郵便番号</label>
                            <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_yubin${index}`)'>COPY</button>
                            <div class="input-group">
                              <button type='button' class='btn btn-outline-secondary' @click='unlock(`od_yubin${index}`,`p_yubin${index}`)'><i class="bi bi-pencil-square"></i></button>
                              <input type='number' v-model='list.yubin' class='form-control' :id='`od_yubin${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"yubin",list.yubin,index)' disabled readonly style='display:none;'>
                              <p style='margin-left:8px;font-size:larger;' :id='`p_yubin${index}`'>{{list.yubin}}</p>
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-12 pt-2'>
                            <label :for='`od_jusho${index}`' class="form-label">住所</label>
                            <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_jusho${index}`)'>COPY</button>
                            <div class="input-group">
                              <button type='button' class='btn btn-outline-secondary' @click='unlock(`od_jusho${index}`,`p_jusho${index}`)'><i class="bi bi-pencil-square"></i></button>
                              <input type='text' v-model='list.jusho' class='form-control' :id='`od_jusho${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"jusho",list.jusho,index)' disabled readonly style='display:none;'>
                              <p style='margin-left:8px;font-size:larger;' :id='`p_jusho${index}`'>{{list.jusho}}</p>
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label :for='`od_tel${index}`' class="form-label">TEL</label>
                            <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_tel${index}`)'>COPY</button>
                            <div class="input-group">
                              <button type='button' class='btn btn-outline-secondary' @click='unlock(`od_tel${index}`,`p_tel${index}`)'><i class="bi bi-pencil-square"></i></button>
                              <input type='tel' v-model='list.tel' class='form-control' :id='`od_tel${index}`' @change='set_order_sts(list.orderNO,"tel",list.tel,index)' disabled readonly style='display:none;'>
                              <p style='margin-left:8px;font-size:larger;' :id='`p_tel${index}`'>{{list.tel}}</p>
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-12'>
                            <label :for='`od_mail${index}`' class="form-label">e-mail</label>
                            <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_mail${index}`)'>COPY</button>
                            <div class="input-group">
                              <button type='button' class='btn btn-outline-secondary' @click='unlock(`od_mail${index}`,`p_mail${index}`)'><i class="bi bi-pencil-square"></i></button>
                              <input type='email' v-model='list.mail' class='form-control' :id='`od_mail${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"mail",list.mail,index)' disabled readonly style='display:none;'>
                              <a :href="`mailto:${list.mail}?subject=受付NO:[${list.orderNO}]のご注文について&body=${list.name} 様%0D%0A%0D%0Aいつもお世話になっております。%0D%0A`">
                                <p style='margin-left:8px;font-size:larger;' :id='`p_mail${index}`'>{{list.mail}}</p>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div v-show='list.発送先有無==="無"'>
                          <button type='button' class='btn btn-secondary' @click='()=>{list.発送先有無="有"; set_order_sts(list.orderNO,"sent_flg",1,index)}'>発送先入力</button>
                        </div>
                        <div v-show='list.発送先有無==="有"'>
                          <hr>
                          <p>【発送先】</p>
                          <div class='row mb-3'>
                            <div class='col-12'>
                              <label :for='`st_atena${index}`' class="form-label">お届け先：お名前・宛名</label>
                              <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_st_name${index}`)'>COPY</button>
                              <div class="input-group">
                                <button type='button' class='btn btn-outline-secondary' @click='unlock(`st_atena${index}`,`p_st_name${index}`)'><i class="bi bi-pencil-square"></i></button>
                                <input type='text' v-model='list.st_name' class='form-control' :id='`st_atena${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"st_name",list.st_name,index)' disabled readonly style='display:none;'>
                                <p style='margin-left:8px;font-size:larger;' :id='`p_st_name${index}`'>{{list.st_name}}</p>
                              </div>
                            </div>
                          </div>
                          <div class='row mb-3'>
                            <div class='col-md-6 col-12'>
                              <label :for='`st_yubin${index}`' class="form-label">お届け先：郵便番号</label>
                              <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_st_yubin${index}`)'>COPY</button>
                              <div class="input-group">
                                <button type='button' class='btn btn-outline-secondary' @click='unlock(`st_yubin${index}`,`p_st_yubin${index}`)'><i class="bi bi-pencil-square"></i></button>
                                <input type='number' v-model='list.st_yubin' class='form-control' :id='`st_yubin${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"st_yubin",list.st_yubin,index)' disabled readonly style='display:none;'>
                                <p style='margin-left:8px;font-size:larger;' :id='`p_st_yubin${index}`'>{{list.st_yubin}}</p>
                              </div>
                            </div>
                          </div>
                          <div class='row mb-3'>
                            <div class='col-12'>
                              <label :for='`st_jusho${index}`' class="form-label">お届け先：住所</label>
                              <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_st_jusho${index}`)'>COPY</button>
                              <div class="input-group">
                                <button type='button' class='btn btn-outline-secondary' @click='unlock(`st_jusho${index}`,`p_st_jusho${index}`)'><i class="bi bi-pencil-square"></i></button>
                                <input type='text' v-model='list.st_jusho' class='form-control' :id='`st_jusho${index}`' placeholder='必須' @change='set_order_sts(list.orderNO,"st_jusho",list.st_jusho,index)' disabled readonly style='display:none;'>
                                <p style='margin-left:8px;font-size:larger;' :id='`p_st_jusho${index}`'>{{list.st_jusho}}</p>
                              </div>
                            </div>
                          </div>
                          <div class='row mb-3'>
                            <div class='col-md-6 col-12'>
                              <label :for='`st_tel${index}`' class="form-label">お届け先：TEL</label>
                              <button type='button' class='btn btn-outline-success icon-btn ms-2' style='margin-top:-4px;' @click='copy_target(`p_st_tel${index}`)'>COPY</button>
                              <div class="input-group">
                                <button type='button' class='btn btn-outline-secondary' @click='unlock(`st_tel${index}`,`p_st_tel${index}`)'><i class="bi bi-pencil-square"></i></button>
                                <input type='tel' v-model='list.st_tel' class='form-control' :id='`st_tel${index}`' @change='set_order_sts(list.orderNO,"st_tel",list.st_tel,index)' disabled readonly style='display:none;'>
                                <p style='margin-left:8px;font-size:larger;' :id='`p_st_tel${index}`'>{{list.st_tel}}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div><!--詳細-->
            </div>
            
          </div>
          <hr>
        </div><!--外枠-->
        
      </template>
    </div>

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="display: none;" id="modalon"></button>
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">件名：{{send_mailsubject}}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <textarea type='memo' class='form-control' rows="25" v-model='send_mailbody'></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='mail_modal_close_osirase'>Close</button>
          <button type='button' class='btn btn-primary' id='mail_send_btn_osirase' @click='send_email()'>{{send_mail_btn}}</button>
        </div>
      </div>
    </div>
  </div>

  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal2" style="display: none;" id="modalon2"></button>
  <!-- Modal -->
  <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">件名：　{{qa_head}}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <textarea type='memo' class='form-control' rows="25" v-model='send_mailbody'></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='mail_modal_close_QA'>Close</button>
          <button type='button' class='btn btn-primary' id='mail_send_btn_QA' @click='send_email_toCustomer()'>{{send_mail_btn}}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel" >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">商品追加</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        <div class='mb-3'>
            <label for='shouhinCD' class="form-label">商品名</label>
            <select class='form-select' id='shouhinCD' v-model='shouhin_Add_index'>
              <template v-for='(S_list,index2) in shouhinMS' :key='S_list.shouhinCD'>
                <option :value='index2'>{{S_list.shouhinNM}}</option>
              </template>
            </select>
          </div>
          <div class='mb-3'>
            <label for='short_info' class="form-label">【商品見出し】</label>
            <!--Pタグ枠あり。-->
            <p id='short_info' style='height:50px; overflow-y:scroll; border:1px solid lightgray;'>{{shouhin_Add.short_info}}</p>
          </div>
          <div class='mb-3'>
            <label for='zei' class="form-label">税区分</label>
					  <select class='form-select' id='zei' v-model='shouhin_Add.zeikbn'>
					  	<option value="0">非課税</option>
					  	<option value="1001">8%</option>
					  	<option value="1101">10%</option>
					  </select>
          </div>
          <div class='mb-3'>
            <label for='tanka' class="form-label">単価</label>
            <input type='number' class='form-control' id='tanka' v-model='shouhin_Add.tanka'>
          </div>
          <div class='mb-3'>
            <label for='su' class="form-label">受注数</label>
            <input type='number' class='form-control' id='su' v-model='shouhin_Add.su'>
          </div>
          <div class='mb-3'>
            <p id='kakaku' class='fs-3'>総額 ￥　{{(Number(shouhin_Add.tanka) * Number(shouhin_Add.su)).toLocaleString()}} -</p>
          </div>
          <div class='mb-3'>
            <label for='bikou' class="form-label">備考</label>
            <textarea type='memo' class='form-control' rows="5" v-model='shouhin_Add.bikou' placeholder="例：追加注文のご連絡対応分　等"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='add_shouhin_modal_close'>キャンセル</button>
          <button type='button' class='btn btn-primary' id='mail_send_btn_QA' @click='add_shouhin()'>登録</button>
        </div>
      </div>
    </div>
  </div>


  </div><!--app-->

  <script src="script/admin_menu.js?<?php echo $time; ?>"></script>
  <script src="script/order_management_vue3.js?<?php echo $time; ?>"></script>
  <script>
    order_mng('order_management.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
    admin_menu('order_management.php','','<?php echo $user_hash;?>').mount('#admin_menu');
  </script>
  <script>// Enterキーが押された時にSubmitされるのを抑制する
      window.onload = function() {
        document.getElementById("app").onkeypress = (e) => {
          // form1に入力されたキーを取得
          const key = e.keyCode || e.charCode || 0;
          if (key == 13) {// 13はEnterキーのキーコード
            //e.preventDefault();// アクションを行わない
          }
        }    
      };    
  </script>
</BODY>
</html>