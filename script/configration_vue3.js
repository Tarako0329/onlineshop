//const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const configration = (Where_to_use,p_token,p_hash) => createApp({//サイト設定
  setup() {
    let token = p_token
    let hash = p_hash
    const yagou = ref('')
    const invoice = ref('')
    const tantou = ref('')
    const shacho = ref('')
    const jusho = ref('')
    const tel = ref('')
    const mail = ref('')
    const cc_mail = ref('')
    const line_id = ref('')
    const fb_id = ref('')
    const x_id = ref('')
    const mail_body_auto = ref('<購入者名> 様\n\nご注文ありがとうございます。\n以下の内容にて、ご注文を受け付けました。\n\n<注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\n※弊社担当にてご注文内容の確認が取れましたら、お支払い・納期等についてのご案内メールを送付いたします。\n※メールが届かない場合、また、不明点・お問い合わせ等ございましたら以下までご連絡くださいませ。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body = ref('<購入者名> 様\n\nご注文ありがとうございます。\n以下の内容にて、ご注文を承りました。\n\n<送料込の注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\nお支払いが確認できましたら発送準備に入ります。\n<支払方法>\n【銀行振込】\n〇〇銀行〇〇支店　普通　0123456\n振込手数料についてはお客様負担となります\n\n【paypay】\n＊＊＊＊＊＊\n\n不明点・お問い合わせ等ございましたら下記へご連絡ください。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body_paid = ref('<購入者名> 様\n\nいつもありがとうございます。\n\n以下のご注文についてのお支払いを確認いたしました。\n<領収書LINK>\n\n発送が終わりましたら再度ご連絡させていただきます。\n\n<送料込の注文内容>\n\n<購入者情報>\n\n何かございましたら以下までご連絡くださいませ。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body_sent = ref('<購入者名> 様\n\nいつもありがとうございます。\n\n以下のご注文について、本日商品を発送いたしました。\n\n<注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\n（お届け先未記載の場合は<購入者名> 様宛にお送りしてます。）\n\n<配送状況>\n\n商品のご到着までしばらくお待ちください。\n\n今後とも <自社名> をよろしくお願いします。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body_cancel = ref('<購入者名> 様\n\nいつもありがとうございます。\n\n以下のご注文について、キャンセルを受け付けました。\n\n<注文内容>\n\n<購入者情報>\n\n今後とも <自社名> をよろしくお願いします。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const site_name = ref('')
    const site_pr = ref('')
    const logo = ref('')
    const loader = ref(false)
    const chk_recept = ref('')
    const chk_sent = ref('')
    const chk_paid = ref('')
    const lock_sts = ref('')
    const cancel_rule = ref('例\n受注生産品について：ご注文受付のメール送信後はキャンセル不可となっております。\n汎用製品について：入金確認後、７日以内でしたらキャンセルを受け付けます。\n返品時の送料についてはご負担願います。')
    const headcolor = ref('')
    const bodycolor = ref('')
    const h_font_color = ref('')

    const site_pr_chk = computed(()=>{
      let msg = ''
      if(site_pr.value.toLowerCase().includes("input")){msg = "inputタグは使えません\n"}
      if(site_pr.value.toLowerCase().includes("<a")){msg = msg + "aタグは使えません\n"}
      if(site_pr.value.toLowerCase().includes("script")){msg = msg + "scriptタグは使えません\n"}
      if(site_pr.value.toLowerCase().includes("form")){msg = msg + "formタグは使えません\n"}
      return msg
    })

    const input_file_btn = (id) =>{//アップロードボタン
      document.getElementById(id).click()
    }
    const uploadfile = (id) =>{//写真アップロード処理・写真をアップしファイルパスを取得
      const params = new FormData();
      
      let i = 0
      while(document.getElementById(id).files[i]!==undefined){
        params.append(`user_file_name_${i}`, document.getElementById(id).files[i]);
        i = i+1
      }
      params.append('fileparam','')
      loader.value = true
      axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="success"){
          logo.value = response.data.filename[0].filename
        }else{
          alert('写真アップロードエラー')
        }
      })
      .catch((error)=>{
        console_log(error)
        alert('写真アップロードERROR')
      })
      .finally(()=>{
        loader.value = false
      })
    }

    const AI_MAIL_CHK = ref('')
    const set_user = () =>{
      if(site_pr_chk.value){
        alert("サイトPRが不正です")
        return;
      }
      loader.value = true
      const form = new FormData();
      form.append(`yagou`, yagou.value)
      form.append(`invoice`, invoice.value)
      form.append(`name`, tantou.value)
      form.append(`shacho`, shacho.value)
      form.append(`jusho`, jusho.value)
      form.append(`tel`, tel.value)
      form.append(`mail`, mail.value)
      form.append(`cc_mail`, cc_mail.value)
      form.append(`line_id`, line_id.value)
      form.append(`fb_id`, fb_id.value)
      form.append(`x_id`, x_id.value)
      form.append(`mail_body`, mail_body.value)
      form.append(`mail_body_auto`, mail_body_auto.value)
      form.append(`mail_body_paid`, mail_body_paid.value)
      form.append(`mail_body_sent`, mail_body_sent.value)
      form.append(`mail_body_cancel`, mail_body_cancel.value)
      form.append(`site_name`, site_name.value)
      form.append(`site_pr`, site_pr.value)
      form.append(`logo`, logo.value)
      form.append(`chk_recept`, chk_recept.value===true?1:0)
      form.append(`chk_sent`, chk_sent.value===true?1:0)
      form.append(`chk_paid`, chk_paid.value===true?1:0)
      form.append(`lock_sts`, lock_sts.value)
      form.append(`cancel_rule`, cancel_rule.value)
      form.append(`headcolor`, headcolor.value)
      form.append(`bodycolor`, bodycolor.value)
      form.append(`h_font_color`, h_font_color.value)
      form.append(`csrf_token`, token)
      form.append(`hash`, hash)

      axios.post("ajax_delins_userMSonline.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        token = response.data.csrf_create

        if(response.data.MSG){alert(response.data.MSG)}
        
        if(response.data.status==="alert-success"){
          loader.value = false
          alert('登録は完了しました。')
          if(response.data.value_check){
            const ck_result = response.data.value_check.check_results
            AI_MAIL_CHK.value = ''
            Object.keys(ck_result).forEach((list,index)=>{
                console_log(list)

                if(ck_result[list]!=='OK'){
                    AI_MAIL_CHK.value = AI_MAIL_CHK.value + `<li style="color:red;">${list}：${ck_result[list]}</li>`
                }
            })
            if(AI_MAIL_CHK.value){
                AI_MAIL_CHK.value = '<div  style="background-color:#fff;border: 1px solid red; animation: blink-red-border-animation 0.5s linear 3;"><p class="pt-3">【AIでチェックしたよ】<p><ul class="mb-2">' + AI_MAIL_CHK.value + '</ul><small>※AIは間違えます。修正は自己判断で！</small></div>'
                alert('自動返信メールの設定に修正推奨箇所があります。')
                document.getElementById('AI_MAIL_CHK').scrollIntoView({ behavior: 'smooth' })
            }
          }
        }else{
          //alert('更新失敗')
        }
      })
      .catch((error)=>{
        console_log(error)
        //console_log(response.data)
        //token = response.data.csrf_create
        alert('更新エラー　：　'+error)
      })
      .finally(()=>{
        loader.value = false
      })
    }

    const get_mail_sample = (template) =>{
      let val=template

      val = val.replace(/<購入者名>/g,'田中次郎')
      val = val.replace(/<注文内容>/g,'【ご注文内容】\n◆商品Ａ\n価格( 10,000 円) x 2(コ) = 合計 20,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆商品Ｂ\n価格( 5,000 円) x 1(コ) = 合計 5,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆消費税：10% = 2,500 円\n◆ご注文総額：27,500円')
      val = val.replace(/<送料込の注文内容>/g,'【ご注文内容】\n◆商品Ａ\n価格( 10,000 円) x 2(コ) = 合計 20,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆商品Ｂ\n価格( 5,000 円) x 1(コ) = 合計 5,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆消費税：10% = 2,500 円\n◆ご注文総額：27,500円\n\n◆配送料：500円 (ヤマト運輸)\n\n御請求額：￥28,000')
      val = val.replace(/<購入者情報>/g,'【ご購入者】\nお名前：田中次郎\n郵便番号：261XXXX\n送付先住所：千葉市美浜区〇〇〇\nTEL：09012341234\nMAIL：sample@gmail.com\nオーダー備考：\nご要望等ございましたらご記入ください。')
      val = val.replace(/<届け先情報>/g,'【お届け先】\nお名前：佐藤次郎\n郵便番号：261XXXX\n送付先住所：千葉市若葉区〇〇〇\nTEL：09012341234')
      val = val.replace(/<自社名>/g,'サンプル株式会社')
      val = val.replace(/<自社住所>/g,'千葉県千葉市稲毛区〇〇〇〇')
      val = val.replace(/<問合せ受付TEL>/g,'0120-00-0000')
      val = val.replace(/<問合せ受付MAIL>/g,'sample@gmail.com')
      val = val.replace(/<問合担当者>/g,'小泉純一郎')
      val = val.replace(/<代表者>/g,'田中角栄')
      val = val.replace(/<配送状況>/g,'配送業者：ヤマト運輸\n配送状況：https://yamatto.co.jp/xxxxxx\n確認番号：0000000000')
      val = val.replace(/<支払方法>/g,'お支払については下記URLよりお願いします。\nhttps://xxxxxxxxxxxx')
      val = val.replace(/<領収書LINK>/g,'領収書は下記URLよりﾀﾞｳﾝﾛｰﾄﾞしてください。\nhttps://xxxxxxxxxxxx')

      return val
    }
    const mail_body_sample = computed(()=>{
      return get_mail_sample(mail_body.value)
    })
    const mail_body_auto_sample = computed(()=>{
      return get_mail_sample(mail_body_auto.value)
    })
    const mail_body_paid_sample = computed(()=>{
      return get_mail_sample(mail_body_paid.value)
    })
    const mail_body_sent_sample = computed(()=>{
      return get_mail_sample(mail_body_sent.value)
    })
    const mail_body_cancel_sample = computed(()=>{
      return get_mail_sample(mail_body_cancel.value)
    })

    const mail_temp_ins = (id,val) =>{
      let textarea = document.getElementById(id);

      let sentence = textarea.value;
      let len      = sentence.length;
      let pos      = textarea.selectionStart;

      let before   = sentence.substr(0, pos);
      let word     = val;
      let after    = sentence.substr(pos, len);

      sentence = before + word + after;

      textarea.value = sentence;

    }
    const line_test = () =>{
      LINE_PUSH(line_id.value,"テスト：「オーダーが入りました」")
    }

    const security_lock = ref(false)

    const chk_bunshou = (p_bunshou,p_type,p_output_id,p_btEl) =>{
      //ajax_chk_gemini.phpにpost接続。パラメータ"Article"にp_bunshouをセット
      console_log(p_btEl.currentTarget)
      
      const btEl = p_btEl.currentTarget// = true
      btEl.disabled = true
      const btSp = p_btEl.currentTarget.querySelector('.spinner-border')// = 'inline-block'
      btSp.style.display = 'inline-block'
      const form = new FormData();
      form.append(`Article`, p_bunshou)
      form.append(`type`, p_type)
      form.append(`answer_type`, 'plain')
      axios.post("ajax_chk_gemini.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.emsg){
          alert(response.data.emsg)
        }else{
          document.getElementById(p_output_id).innerHTML = response.data.result.replace("```html","").replace("```","")
        }
      })
      .catch((error,response)=>{
        console_log(error)
        alert('チェックerror')
      })
      .finally(()=>{
        console_log('run finally')
        btSp.style.display = 'none'
        btEl.disabled = false
      })
    }

    const copy_url = (p_url_id) => {
      //ID p_url_id の値をクリップボードにコピー
      const copyText = document.getElementById(p_url_id).value;
      navigator.clipboard.writeText(copyText).then(() => {
        alert('URLをコピーしました');
      }).catch(err => {
        console.error('コピーに失敗しました: ', err);
        alert('URLのコピーに失敗しました');
      });
      
    }

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS"){
      }

      GET_USER2()
      .then((response)=>{
        console_log(response.status)
        if(response.status!=="alert-success"){
          alert(response.msg)
          security_lock.value=true
        }
        yagou.value = response.Users_online[0].yagou
        invoice.value = response.Users_online[0].invoice
        site_name.value = response.Users_online[0].site_name
        site_pr.value = response.Users_online[0].site_pr
        logo.value = response.Users_online[0].logo
        tantou.value = response.Users_online[0].name
        shacho.value = response.Users_online[0].shacho
        jusho.value = response.Users_online[0].jusho
        tel.value = response.Users_online[0].tel
        mail.value = response.Users_online[0].mail
        cc_mail.value = response.Users_online[0].cc_mail
        line_id.value = response.Users_online[0].line_id
        fb_id.value = response.Users_online[0].fb_id
        x_id.value = response.Users_online[0].x_id
        chk_recept.value = response.Users_online[0].chk_recept===1?true:false
        chk_sent.value = response.Users_online[0].chk_sent===1?true:false
        chk_paid.value = response.Users_online[0].chk_paid===1?true:false
        lock_sts.value = response.Users_online[0].lock_sts
        headcolor.value = response.Users_online[0].headcolor
        bodycolor.value = response.Users_online[0].bodycolor
        h_font_color.value = response.Users_online[0].h_font_color
        if(response.Users_online[0].mail_body!=="''"){
          mail_body.value = response.Users_online[0].mail_body
        }
        if(response.Users_online[0].mail_body_auto!=="''"){
          mail_body_auto.value = response.Users_online[0].mail_body_auto
        }
        if(response.Users_online[0].mail_body_paid!=="''"){
          mail_body_paid.value = response.Users_online[0].mail_body_paid
        }
        if(response.Users_online[0].mail_body_sent!=="''"){
          mail_body_sent.value = response.Users_online[0].mail_body_sent
        }
        if(response.Users_online[0].mail_body_cancel!=="''"){
          mail_body_cancel.value = response.Users_online[0].mail_body_cancel
        }
        if(response.Users_online[0].cancel_rule!=="''"){
          cancel_rule.value = response.Users_online[0].cancel_rule
        }

      })
    })

    return{
      yagou,
      invoice,
      tantou,
      shacho,
      jusho,
      tel,
      mail,
      cc_mail,
      line_id,
      fb_id,
      x_id,
      site_name,
      site_pr,
      site_pr_chk,
      logo,
      set_user,
      loader,
      mail_body_auto,
      mail_body,
      mail_body_sent,
      mail_body_paid,
      mail_body_auto_sample,
      mail_body_sample,
      mail_body_sent_sample,
      mail_body_paid_sample,
      mail_body_cancel,
      mail_body_cancel_sample,
      chk_paid,
      chk_recept,
      chk_sent,
      lock_sts,
      cancel_rule,
      line_test,
      input_file_btn,
      uploadfile,
      mail_temp_ins,
      security_lock,
      chk_bunshou,
      AI_MAIL_CHK,
      headcolor,
      bodycolor,
      h_font_color,
      copy_url,
    }
  }
})

