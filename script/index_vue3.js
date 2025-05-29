const sales = (Where_to_use,p_token,p_user_id) => createApp({//販売画面
  setup() {
    let token = p_token
    const msg=ref('')
    const loader = ref(false)

    const shouhinMS = ref([])
    const shouhinMS_pic = ref([])
    
    const mode = ref('new')

    const get_shouhinMS_online = (p_serch) => {//商品マスタ取得
      //index.php実行時にGET[key]がセットされてると$SESSIONにUIDがセットされ、
      //自動でUID分の商品のみが返ってくる
      //p_serch は 商品名
      axios
      .get(`ajax_get_shouhinMS_online.php?f=${p_serch}`)
      .then((response) => {
        if(response.data.alert==="success"){
          shouhinMS.value = [...response.data.dataset]
          shouhinMS_pic.value = [...response.data.pic_set]
          console_log('get_shouhinMS_online succsess')
          //console_log(response.data.pic_set)

          IDD_Read_All(tableNM,(cart)=>{//indexDBのカートの内容を反映
            cart.forEach((list)=>{
              shouhinMS.value.forEach((slist,index)=>{
                if(list.id === slist.uid + '-' + slist.shouhinCD){
                  shouhinMS.value[index].ordered = list.ordered
                  return
                }
              })
            })
          })
          if(sessionStorage.getItem('from')==='product_cart'){
            loader.value = true
            let id = sessionStorage.getItem('from_uid')
            console_log(`productページから来たよ:${sessionStorage.getItem('from_uid')}`)
            setTimeout(()=>{
              loader.value = false
              ordering(Number(id))
            },"500")
          }

        }else{
          console_log('get_shouhinMS_online succsess:NoData')
        }
      })
      .catch((error)=>{
        console_log('get_shouhinMS_online.php ERROR')
        console_log(error)
      })
      .finally(()=>{
        //loader.value = false
        sessionStorage.clear();
      })
    }

    const Charge_amount_by_store = computed(()=>{//ショップごとの注文合計額
      let kingakus = []
      let kingaku_temp = new Decimal(0)

      shouhinMS.value.forEach((row,index)=>{
        let num1 = new Decimal(row.zeikomikakaku);
        let num2 = new Decimal(row.ordered);
        //console_log(num1.mul(num2).toNumber());

        if(row.ordered != 0){
          kingaku_temp = kingaku_temp.add(num1.mul(num2)) //税込合計
        }
        //if(index === (shouhinMS.value.length - 1) && kingaku_temp.toNumber() !== 0){
        if(((index === (shouhinMS.value.length - 1) && kingaku_temp.toNumber() !== 0) 
            || index !== (shouhinMS.value.length - 1) && row.uid !== shouhinMS.value[index+1].uid && kingaku_temp.toNumber() !== 0)){
          kingakus.push({
            "uid":row.uid
            ,"yagou":row.yagou
            ,"logo":row.logo
            ,"tel":row.tel
            ,"mail":row.mail
            ,"cancel_rule":row.cancel_rule
            ,"seikyu":kingaku_temp.toNumber()
          })
          
          kingaku_temp = Decimal(0)
        }
      })

      if(kingakus.length===0){
        kingakus.push({
          "uid":9999999
          ,"yagou":'shop'
          ,"logo":''
          ,"tel":'None'
          ,"mail":'None'
          ,"cancel_rule":"None"
          ,"seikyu":0
        })
      }

      return kingakus.filter((row) =>{
        if(order_shop_id.value === ''){
          return (row.ordered!=0)
        }else{
          return (row.ordered!=0 && row.uid === order_shop_id.value)
        }
      })
    })

    const get_ordered = computed(()=>{//注文リスト
      let orderlist = []
      orderlist = shouhinMS.value.filter((row)=>{
        //return (row.ordered!=0)
        if(order_shop_id.value === ''){
          return (row.ordered!=0)
        }else{
          return (row.ordered!=0 && row.uid === order_shop_id.value)
        }
      })

      orderlist.forEach((row)=>{
        let num1 = new Decimal(row.zeikomikakaku);
        let num2 = new Decimal(row.ordered);
        let num3 = new Decimal(row.tanka);
        //console_log(num1.mul(num2).toNumber());
        row.goukeikingaku = num1.mul(num2).toNumber() //税込合計
        row.goukeitanka = num3.mul(num2).toNumber()   //単価合計
      })

      return orderlist
    })

    const search_word = ref('')
    const serch_type = ref('商品名＋説明文') //or 商品名
    const shouhinMS_SALE = computed(()=>{
      if(search_word.value==''){
        return shouhinMS.value.filter((row)=>{
          return (row.status==='show' || row.status==='limited' || row.status==='soldout')
        })
      }else{
        if(serch_type.value==="商品名"){
          return shouhinMS.value.filter((row)=>{
            return row.shouhinNM.includes(search_word.value) && (row.status==='show' || row.status==='limited' || row.status==='soldout')
          })        
        }else if(serch_type.value==="商品名＋説明文"){
          return shouhinMS.value.filter((row)=>{
            let words = row.shouhinNM + row.short_info + row.infomation
            return words.includes(search_word.value) && (row.status==='show' || row.status==='limited' || row.status==='soldout')
          })        
        }
      }
    })

    const remove_limit = (p_index) =>{
      if(shouhinMS_SALE.value[p_index].limited_cd === shouhinMS_SALE.value[p_index].limited_cd_nyuryoku){
        shouhinMS_SALE.value[p_index].status = "show"
      }
    }
 
    const order_kakaku = ref(0) //オーダー税込総額
    const order_shop_id = ref('')
    const od_atena = ref('')
    const od_yubin = ref('')
    const od_jusho = ref('')
    const od_tel = ref('')
    const od_mail = ref('')
    const od_bikou = ref('')

    const order_sent_same = ref(true) //注文者と送付先が同一(falseは別)
    const st_atena = ref('')
    const st_yubin = ref('')
    const st_jusho = ref('')
    const st_tel = ref('')
    const order_count =(index,val) =>{//注文画面の数量増減ボタン
      let order = Number(shouhinMS_SALE.value[index].ordered)
      if(order + Number(val) < 0){
        shouhinMS_SALE.value[index].ordered = 0
      }else{
        shouhinMS_SALE.value[index].ordered = order + Number(val)
        let tanka = new Decimal(Number(shouhinMS_SALE.value[index].tanka))
        let shouhizei = new Decimal(Number(shouhinMS_SALE.value[index].shouhizei))
        let order_kin = new Decimal(order_kakaku.value)
        let zougen = new Decimal(val)
        order_kakaku.value = order_kin.add(zougen.mul(tanka.add(shouhizei))).toNumber()
      }
      console_log(order_kakaku.value)
      IDD_Write(tableNM,[{
        id:String(shouhinMS_SALE.value[index].uid) + '-' + String(shouhinMS_SALE.value[index].shouhinCD)
        ,shop_id:shouhinMS_SALE.value[index].uid
        ,shouhinCD:shouhinMS_SALE.value[index].shouhinCD
        ,ordered:shouhinMS_SALE.value[index].ordered
      }])

    }
    const ordered_count =(index,val) =>{//注文確認画面の数量増減ボタン
      let order = Number(get_ordered.value[index].ordered)
      if(order + Number(val) < 0){
        get_ordered.value[index].ordered = 0
      }else{
        let tanka = new Decimal(Number(get_ordered.value[index].tanka))
        let shouhizei = new Decimal(Number(get_ordered.value[index].shouhizei))
        let order_kin = new Decimal(order_kakaku.value)
        let zougen = new Decimal(val)
        order_kakaku.value = order_kin.add(zougen.mul(tanka.add(shouhizei))).toNumber()

        IDD_Write(tableNM,[{
          id:String(get_ordered.value[index].uid) + '-' + String(get_ordered.value[index].shouhinCD)
          ,shop_id:get_ordered.value[index].uid
          ,shouhinCD:get_ordered.value[index].shouhinCD
          ,ordered:get_ordered.value[index].ordered + Number(val)
        }])

        get_ordered.value[index].ordered = order + Number(val)
        //console_log(`件数：${get_ordered.value.length}`)
        if(get_ordered.value.length===0){
          //console_log(`注文消えた！`)
          alert('カートが空です。ショッピング画面に戻ります。')
          order_shop_id.value = ''
          btn_name.value='カート'
          mode.value="shopping"
        }else{
          //console_log(`注文消えてない！`)
        }
      }
      console_log(order_kakaku.value)
      /*
      IDD_Write(tableNM,[{
        id:String(get_ordered.value[index].uid) + '-' + String(get_ordered.value[index].shouhinCD)
        ,shop_id:get_ordered.value[index].uid
        ,shouhinCD:get_ordered.value[index].shouhinCD
        ,ordered:get_ordered.value[index].ordered
      }])
      */
    }

    const btn_name = ref('カート')
    const ordering = (uid) =>{
      console_log(`ordering:${uid}`)
      if(mode.value==="shopping"){
        order_shop_id.value = uid
        btn_name.value='戻る'
        mode.value="ordering"
      }else if(mode.value==="ordering"){
        order_shop_id.value = ''
        btn_name.value='カート'
        mode.value="shopping"
      }

    }

    watch(msg,()=>{
      console_log('watch msg => '+msg.value)
      setTimeout(()=>{msg.value=""}, 3000);//3s
      
    })

    const orderNO = ref('')
    const order_submit = () =>{//注文送信
      let msg = ''
      if(order_shop_id.value===''){
        alert('想定外エラー：order_shop_id が選択されてません')
        return
      }
      if(od_atena.value==''){
        msg = ' 宛名'
      }
      if(od_yubin.value==''){
        msg = msg + ' 郵便番号'
      }
      if(od_jusho.value==''){
        msg = msg + ' 住所'
      }
      if(od_tel.value==''){
        msg = msg + ' TEL'
      }
      if(od_mail.value==''){
        msg = msg + ' メールアドレス'
      }
      if(order_sent_same.value === false){
        if(st_atena.value==''){
          msg = msg + ' お届け先宛名'
        }
        if(st_yubin.value==''){
          msg = msg + ' お届け先郵便番号'
        }
        if(st_jusho.value==''){
          msg = msg + ' お届け先住所'
        }
      }
      if(String(msg).length!==0){
        alert(`${msg} を入力して下さい。`)
        return
      }
      if(confirm('この内容で送信してよいですか？')===false){
        return
      }
      loader.value = true

      const form = new FormData();
      form.append(`order_shop_id`, order_shop_id.value)
      form.append(`name`, od_atena.value)
      form.append(`yubin`, String(od_yubin.value))
      form.append(`jusho`, od_jusho.value)
      form.append(`tel`, String(od_tel.value))
      form.append(`mail`, od_mail.value)
      form.append(`bikou`, od_bikou.value)
      form.append(`csrf_token`, token)

      form.append(`st_name`, st_atena.value)
      form.append(`st_yubin`, String(st_yubin.value))
      form.append(`st_jusho`, st_jusho.value)
      form.append(`st_tel`, String(st_tel.value))

      let i = 0
      get_ordered.value.forEach((row)=>{
        form.append(`meisai[${i}][shouhinCD]`,row.shouhinCD)
        form.append(`meisai[${i}][shouhinNM]`,row.shouhinNM)
        form.append(`meisai[${i}][su]`,row.ordered)
        form.append(`meisai[${i}][tanka]`,row.tanka)
        form.append(`meisai[${i}][zei]`,row.shouhizei)
        form.append(`meisai[${i}][zeikbn]`,row.zeikbn)
        form.append(`meisai[${i}][bikou]`,row.customer_bikou)
        form.append(`meisai[${i}][goukeitanka]`,row.goukeitanka)
        form.append(`meisai[${i}][short_info]`,row.short_info)
        i=i+1
      })
      axios.post("ajax_ins_order.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="alert-success"){
          //alert('ありがとうございます。ご注文を受け付けました。')
          mode.value='ordered'
          orderNO.value=response.data.orderNO
          document.getElementById('spy2').click()
          document.getElementById('header').style.pointerEvents = 'none'
          document.getElementById('navbarNav').style.opacity = 0
          shouhinMS.value.forEach((row)=>{
            if(row.uid === order_shop_id.value){
              IDD_Delete(tableNM,row.uid + '-' + row.shouhinCD)
            }
          })
              
        }else{
          alert('注文送信失敗')
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        token = response.data.csrf_create
      })
      .finally(()=>{
        loader.value = false
      })

    } 
    const order_clear =()=>{//注文クリア
      mode.value='shopping'
      shouhinMS.value.forEach((row)=>{
        if(row.uid === order_shop_id.value){
          row.ordered=0
          //IDD_Delete(tableNM,row.uid + '-' + row.shouhinCD)
        }
      })
      order_shop_id.value = ''
      btn_name.value='カート'
      order_kakaku.value=0
      document.getElementById('header').style.pointerEvents = 'auto'
      document.getElementById('navbarNav').style.opacity = 1
    }

    const img_zoom =ref(false)
    const pic_zoom_uid = ref(0)
    const pic_zoom_cd = ref(0)
    const pic_zoom = (uid,shouhinCD) =>{
      console_log(`pic_zoom:${shouhinCD}`)
      pic_zoom_cd.value = Number(shouhinCD)
      pic_zoom_uid.value = Number(uid)
      if(img_zoom.value){
        img_zoom.value = false
      }else{
        img_zoom.value = true
      }
      console_log(`pic_zoom:${pic_zoom_cd}`)
    }
    const shouhinMS_pic_sel = computed(()=>{
      const rtn = shouhinMS_pic.value.filter((row)=>{
        if(row.shouhinCD===pic_zoom_cd.value && row.uid===pic_zoom_uid.value){return true}
      })
      return rtn
    })

    const qa_index = ref(0)
    const qa_yagou = ref('')
    const qa_shouhinNM = ref('')
    const qa_name = ref('')
    const qa_mail = ref('')
    const qa_text = ref('')
    const qa_head = ref('')
    const set_qa_index = (p_index) => {
      qa_index.value = p_index
      qa_yagou.value = shouhinMS_SALE.value[p_index].yagou
      qa_shouhinNM.value = shouhinMS_SALE.value[p_index].shouhinNM
      qa_head.value = qa_shouhinNM.value
      
    }
    const send_email = () =>{
      if(confirm('お問い合わせ内容に変更はないですか？')){
      }else{
        return
      }
      
      loader.value = true
      
      document.getElementById('mail_send_btn').disabled = true

      const form = new FormData();
      form.append(`mailto`, qa_mail.value)
      //form.append(`mailtoBCC`, shouhinMS_SALE.value[qa_index.value].mail)
      //form.append(`lineid`, shouhinMS_SALE.value[qa_index.value].line_id)
      form.append(`shop_id`, shouhinMS_SALE.value[qa_index.value].uid)
      form.append(`qa_head`, qa_head.value)
      form.append(`qa_name`, qa_name.value)
      form.append(`subject`, `【${qa_yagou.value}】ご質問を受付ました「${qa_head.value}」`)
      form.append(`mailbody`, `※このメールは送信専用です。返信しても出店者には届きません。※\nお問い合わせ内容\n\n${qa_text.value}`)
      form.append(`qa_text`, qa_text.value)
      form.append(`sts`, "Q")
      form.append(`csrf_token`, token)
      //form.append(`hash`, hash)

      axios.post("ajax_sendmail_custmor.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        loader.value = false
        token = response.data.csrf_create
        if(response.data.status==="alert-success"){
          qa_text.value=""
          alert('メールを送信しました')
          document.getElementById('mail_modal_close').click()
        }else{
          alert('送信失敗')
        }
        
      })
      .catch((error,response)=>{
        console_log(error)
        token = response.data.csrf_create
      })
      .finally(()=>{
        loader.value = false
        document.getElementById('mail_send_btn').disabled = false
      })

    }

    const product_url = ref(`${HTTP}product.php?id=`)
    const open_list = []

    const shouhin_open_log = (p_uid,p_shouhinCD,p_index) =>{
      console_log(`shouhin_open_log : start`)
      console_log(`shouhin_open_log : ${open_list}`)
      if(open_list.indexOf(`${p_uid}-${p_shouhinCD}`)!==-1){
        console_log(`shouhin_open_log : open->close`)
        open_list.splice(open_list.indexOf(`${p_uid}-${p_shouhinCD}`),1)
        console_log(`shouhin_open_log : ${open_list}`)
        return 0
      }
      
      const form = new FormData();
      form.append(`shouhinCD`, `${p_uid}-${p_shouhinCD}`)
      form.append(`csrf_token`, token)
      
      axios.post("ajax_ins_access_log.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        open_list.push(`${p_uid}-${p_shouhinCD}`)
      })
      .catch((error,response)=>{
        console_log(error)
      })
      .finally(()=>{
        console_log(`shouhin_open_log : ${open_list}`)
      })
    }

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)

      if(Where_to_use==="index"){
        get_shouhinMS_online("%")
        mode.value='shopping'
        document.getElementById("menu_home").classList.add("active");
      }
    })

    return{
      msg,
      loader,
      mode,
      shouhinMS_pic,
      get_shouhinMS_online,
      order_count,
      ordered_count,
      order_kakaku,
      Charge_amount_by_store,
      order_shop_id,
      od_atena,
      od_yubin,
      od_jusho,
      od_tel,
      od_mail,
      od_bikou,
      order_sent_same,
      st_atena,
      st_yubin,
      st_jusho,
      st_tel,
      btn_name,
      ordering,
      get_ordered,
      shouhinMS_SALE,
      remove_limit,
      order_submit,
      orderNO,
      order_clear,
      img_zoom,
      pic_zoom,
      shouhinMS_pic_sel,//写真拡大
      search_word,
      serch_type,
      qa_index,
      qa_yagou,
      qa_shouhinNM,
      qa_mail,
      qa_name,
      qa_head,
      qa_text,
      set_qa_index,
      send_email,
      product_url,
      shouhin_open_log,
    }
  }
});
