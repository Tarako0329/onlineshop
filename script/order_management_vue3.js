const order_mng = (Where_to_use,p_token,p_hash) => createApp({//販売管理
  setup() {
    let token = p_token
    let hash = p_hash
    let haita_flg = 'stop'//set_order_stsをシングルスレッドに

    const orderlist_hd = ref([])
    const orderlist_bd = ref([])
    const get_orderlist = () =>{
      axios
      .get(`ajax_get_orderlists.php`)
      .then((response) => {
        if(response.data.alert==="success"){
          orderlist_hd.value = [...response.data.header]
          orderlist_bd.value = [...response.data.body]
          console_log('ajax_get_orderlists succsess')
          console_log(response.data)
        }else{
          console_log('ajax_get_orderlists succsess:NoData')
        }
      })
      .catch((error)=>{
        console_log('ajax_get_orderlists.php ERROR')
        console_log(error)
      })
      .finally(()=>{
        //loader.value = false
      })
    }

    const set_order_sts = (orderNO,colum,val,index) =>{//受注情報の更新
      if(haita_flg !== 'stop'){
        console_log('set_order_sts：排他')
        return
      }
      haita_flg = 'start'
      loader.value = true
      //colum項目にvalを設定
      console_log(orderNO)
      console_log(colum)
      console_log(orderlist_hd.value[index])
      if(colum==="cancel" && val===1){
        if(confirm("ご注文をキャンセルしてよいですか？")===false){
          loader.value = false
          return
        }
      }

      const form = new FormData();
      form.append(`orderNO`, orderNO)
      form.append(`colum`, colum)
      form.append(`value`, val)
      form.append(`csrf_token`, token)
      form.append(`hash`, hash)

      axios.post("ajax_upd_order_h.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        token = response.data.csrf_create
        console_log(response.data)
        send_mailsubject.value = `【${site_name.value}】ご注文についてのご連絡「受付番号：${orderNO}」`
        if(response.data.status==="alert-success"){
          if((colum==="payment" || colum==="sent" || colum==="first_answer") && val===1){
            if(confirm('メール送信画面を開きますか？')){
              send_index.value = index
              send_orderNO.value = orderNO
              if(colum==="payment" && val===1){
                send_mailbody.value=get_mail_sample(mail_body_paid.value,index)
                document.getElementById("modalon").click()
              }
              if(colum==="sent" && val===1){
                if(String(orderlist_hd.value[index].postage_url).length<9){
                  if(confirm("配送確認URLは未入力のままでよいですか？")===false){
                    document.getElementById(`postage_url_${index}`).style.backgroundColor="rgb(243, 149, 235)"
                    return
                  }
                }
                if(String(orderlist_hd.value[index].postage_no).length<=1){
                  if(confirm("配送確認番号は未入力のままでよいですか？")===false){
                    document.getElementById(`postage_no_${index}`).style.backgroundColor="rgb(243, 149, 235)"
                    return
                  }
                }
                document.getElementById(`postage_url_${index}`).style.backgroundColor="#fff"
                document.getElementById(`postage_no_${index}`).style.backgroundColor="#fff"

                send_mailbody.value=get_mail_sample(mail_body_sent.value,index)
                document.getElementById("modalon").click()
              }
              if(colum==="first_answer" && val===1){
                if(confirm(`配送業者『${orderlist_hd.value[index].post_corp}』　送料 ${orderlist_hd.value[index].postage} 円でよろしいですか？`)===false){
                  document.getElementById(`postage${index}`).style.backgroundColor="rgb(243, 149, 235)"
                  document.getElementById(`post_corp${index}`).style.backgroundColor="rgb(243, 149, 235)"
                  //return
                }else{
                  document.getElementById(`postage${index}`).style.backgroundColor="#fff"
                  document.getElementById(`post_corp${index}`).style.backgroundColor="#fff"
                  send_mailbody.value=get_mail_sample(mail_body.value,index)
                  document.getElementById("modalon").click()
                }
              }
            }
          }
          if(colum==="cancel" && val===1){
            alert("キャンセルしました")
            orderlist_hd.value[index].cancel=1
          }
        }else{
          alert('更新失敗')
        }
        //token = response.data.csrf_create
      })
      .catch((error)=>{
        console_log(error)
        //console_log(response)
        //token = response.data.csrf_create
      })
      .finally(()=>{
        loader.value = false
        haita_flg = 'stop'
      })
    }

    const unlock = (id,id2)=>{
      document.getElementById(id).disabled = false
      document.getElementById(id).readOnly = false
      document.getElementById(id).style.display = 'block'
      document.getElementById(id2).style.display = 'none'
    }

    const copy_target = (id) =>{
      COPY_TARGET(id)
    }

    //サイト設定情報
    const yagou = ref('')
    const site_name = ref('')
    const tantou = ref('')
    const shacho = ref('')
    const jusho = ref('')
    const tel = ref('')
    const mail = ref('')
    const cc_mail = ref('')
    const mail_body = ref('')
    const mail_body_paid = ref('')
    const mail_body_sent = ref('')
    const mail_body_cancel = ref('') 
    const chk_paid = ref('')
    const chk_recept = ref('')
    const chk_sent = ref('')
    const loader = ref(false)

    //mail関連
    const send_mailbody = ref('')
    const send_mailsubject = ref('')
    const send_index = ref(0)
    const send_orderNO = ref(0)
    const send_mail_btn = ref('送 信')

    const get_mail_sample = (template,index)=>{
      //console_log(`get_mail_sample:${template}`)
      let val = template
      let orders = ''
      let orders_postage = ''
      let post_info = ''

      let row = orderlist_hd.value[index]
      
      //明細取得
      orders = '【ご注文内容】\n'
      orderlist_bd.value.forEach((row2,index)=>{
        if(row.orderNO===row2.orderNO && row2.zei==="0.00"){
          orders = orders + "◆" + row2.shouhinNM + "\n価格( " + String(Number(row2.tanka).toLocaleString()) + " 円) x " + String(row2.su) + "(コ) = 合計 " + String(Number(row2.goukeitanka).toLocaleString()) + " 円(税抜)\n備考：" + row2.bikou + "\n\n";
        }else if(row.orderNO===row2.orderNO && row2.zei!=="0.00"){
          //消費税
          orders = orders + "消費税：" + row2.shouhinNM + " = " + String(Number(row2.zei).toLocaleString()) + " 円\n"
        }
      })
      orders = orders + "ご注文総額： " + String(Number(row.税込総額).toLocaleString()) + " 円(税込)"
      
      orders_postage = orders + "\n\n◆送料：" + String(Number(row.postage).toLocaleString()) + " 円 （" + row.post_corp + "） \n\n御請求額："+String((Number(row.税込総額)+Number(row.postage)).toLocaleString())+"円"
      
      if(String(row.post_corp).length > 1){post_info = post_info + `配送業者：${row.post_corp}\n`}
      if(String(row.postage_url).length > 9){post_info = post_info + `ＵＲＬ：${row.postage_url}\n`} // https:// で 8文字なので
      if(String(row.postage_no).length > 1){post_info = post_info + `確認番号：${row.postage_no}\n`}

      val = val.replace(/<購入者名>/g,row.name)
      val = val.replace(/<注文内容>/g,orders)
      val = val.replace(/<送料込の注文内容>/g,orders_postage)
      val = val.replace(/<購入者情報>/g,'【ご購入者】\nお名前：' + row.name + '\n郵便番号：' + row.yubin + '\n住所：' + row.jusho + '\nTEL：' + row.tel + '\nMAIL：' + row.mail + '\nオーダー備考：\n' + row.bikou + '')
      val = val.replace(/<届け先情報>/g,'【お届け先】\nお名前：' + row.st_name + '\n郵便番号：' + row.st_yubin + '\n送付先住所：' + row.st_jusho + '\nTEL：' + row.st_tel + '')
      val = val.replace(/<自社名>/g,yagou.value)
      val = val.replace(/<自社住所>/g,jusho.value)
      val = val.replace(/<問合せ受付TEL>/g,tel.value)
      val = val.replace(/<問合せ受付MAIL>/g,mail.value)
      val = val.replace(/<問合担当者>/g,tantou.value)
      val = val.replace(/<代表者>/g,shacho.value)
      val = val.replace(/<配送状況>/g,post_info)
      val = val.replace(/<支払方法>/g,`お支払については下記URLよりお願いします。\n${HTTP}payment.php?key=${hash}&val=${String((Number(row.税込総額)+Number(row.postage)).toLocaleString())}`)
      val = val.replace(/<領収書LINK>/g,`領収書は下記URLよりﾀﾞｳﾝﾛｰﾄﾞしてください。\n${HTTP}pdf_receipt.php?hash=${hash}&val=${row.orderNO*9999}`)
      
      return val
    }

    const send_email = () =>{
      if(confirm('メールを送付しますか？')){
      }else{
        return
      }
      
      //念のための確認
      if(send_orderNO.value!==orderlist_hd.value[send_index.value].orderNO){
        alert('受付番号不一致')
        return
      }

      loader.value = true
      send_mail_btn.value='送信中'

      const form = new FormData();
      form.append(`mailto`, orderlist_hd.value[send_index.value].mail)
      form.append(`mailtoCC`, cc_mail.value)
      form.append(`subject`, `【${site_name.value}】ご注文についてのご連絡「受付番号：${send_orderNO.value}」`)
      form.append(`mailbody`, `※このメールは送信専用です。返信しても出店者には届きません。※\n${send_mailbody.value}`)
      form.append(`csrf_token`, token)
      form.append(`hash`, hash)

      axios.post("ajax_sendmail.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        loader.value = false
        if(response.data.status==="alert-success"){
          token = response.data.csrf_create
          alert('メールを送信しました')
          document.getElementById('mail_modal_close').click()
        }else{
          alert('送信失敗')
          token = response.data.csrf_create
        }
        
      })
      .catch((error,response)=>{
        console_log(error)
        token = response.data.csrf_create
      })
      .finally(()=>{
        loader.value = false
        send_mail_btn.value='送 信'
      })

    }

    const cancel_lock = computed(()=>{
      let retn = []
      orderlist_hd.value.forEach((row,index)=>{
        let sts = 'unlock'
        if(row.lock_sts === "recept" && row.オーダー受付==="済"){sts = 'lock'}
        if(row.lock_sts === "paid" && row.入金==="済"){sts = 'lock'}
        if(row.lock_sts === "sent" && row.発送==="済"){sts = 'lock'}
        retn.push({
          "uid":row.uid,
          "orderNO":row.orderNO,
          "cancel":sts
        })
      })
      return retn
    })

    const serch_word = ref('')
    const serch_mail = ref('')
    const set_serch_mail = () =>{
      serch_mail.value = order_hd_serch.value[0].mail
    }
    const order_hd_serch = computed(()=>{
      return orderlist_hd.value.filter((row)=>{
        return row.orderNO == serch_word.value || row.mail === serch_mail.value
      })
    })

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="order_rireki.php"){
        document.getElementById("menu_rireki").classList.add("active");
      }else if(Where_to_use==="order_management.php"){
      }
      get_orderlist()
      GET_USER2()
      .then((response)=>{
        console_log(response)
        yagou.value = response.Users_online[0].yagou
        site_name.value = response.Users_online[0].site_name
        tantou.value = response.Users_online[0].name
        shacho.value = response.Users_online[0].shacho
        jusho.value = response.Users_online[0].jusho
        tel.value = response.Users_online[0].tel
        mail.value = response.Users_online[0].mail
        cc_mail.value = response.Users_online[0].cc_mail
        mail_body.value = response.Users_online[0].mail_body
        mail_body_paid.value = response.Users_online[0].mail_body_paid
        mail_body_sent.value = response.Users_online[0].mail_body_sent
        chk_recept.value = response.Users_online[0].chk_recept===1?true:false
        chk_sent.value = response.Users_online[0].chk_sent===1?true:false
        chk_paid.value = response.Users_online[0].chk_paid===1?true:false
      })
    })

    return{
      orderlist_hd,
      orderlist_bd,
      get_orderlist,
      set_order_sts,
      unlock,
      yagou,
      tantou,
      shacho,
      jusho,
      tel,
      mail,
      mail_body,
      mail_body_paid,
      mail_body_sent,
      mail_body_cancel,
      send_email,
      loader,
      send_mailbody,
      send_mailsubject,
      chk_paid,
      chk_recept,
      chk_sent,
      cancel_lock,
      serch_word,
      order_hd_serch,
      serch_mail,
      set_serch_mail,
      send_mail_btn,
      copy_target,
    }
  }
})

