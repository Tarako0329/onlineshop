const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const shouhinMS = (Where_to_use,p_token) => createApp({
  setup() {
    const zeiMS = [
      {
        zeikbn:"0",
        ritu:0
      },
      {
        zeikbn:"1001",
        ritu:0.08
      },
      {
        zeikbn:"1101",
        ritu:0.1
      },
    ]
    let token = p_token
    const msg=ref('')

    const shouhinMS = ref([])
    const shouhinMS_pic = ref([])
    
    const mode = ref('new')
    const get_shouhinMS = (serch) => {
      let url=`ajax_get_shouhinMS.php?f=${serch}`
      console_log('get_shouhinMS start')
      
      axios.get(url)
      .then((response) => {
        console_log(response.data)
        shouhinMS.value = [...response.data.dataset]
        console_log('get_shouhinMS succsess')
      })
      .catch((error)=>{
        console_log(error)
        //alert('リターンエラー：商品マスタ取得失敗')
      })
      .finally(()=>{
        //loader.value = false
      })
    }
    const get_shouhinMS_newcd = () => {
      let url=`ajax_get_shouhinMS_newcd.php`
      console_log('get_shouhinMS_newcd start')
      
      axios.get(url)
      .then((response) => {
        console_log(response.data)
        shouhinCD.value = response.data
        console_log('get_shouhinMS_newcd succsess')
      })
      .catch((error)=>{
        console_log(error)
        alert('リターンエラー：商品マスタnewCD取得失敗')
      })
      .finally(()=>{
        //loader.value = false
      })
    }
    
    const shouhinCD = ref('')
    const shouhinNM = ref('')
    const status = ref('show')
    const tanka = ref(0)
    const zei = ref(1101)
    const midasi = ref('')
    const info = ref('')
    const customer_bikou = ref('ご要望等ございましたらご記入ください。')
    const pic_list = ref([])
    const rez_shouhinCD = ref('')
    const rez_shouhinNM = ref('')
    const get_shouhinMS_online = (serch) => {
      axios
      .get(`ajax_get_shouhinMS_online.php?f=${serch}`)
      .then((response) => {
        if(response.data.alert==="success"){
          shouhinMS.value = [...response.data.dataset]
          shouhinMS_pic.value = [...response.data.pic_set]
          console_log('get_shouhinMS_online succsess')
          console_log(response.data.pic_set)
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
      })
    }

    const get_ordered = computed(()=>{
      let orderlist = []
      orderlist = shouhinMS.value.filter((row)=>{
        return (row.ordered!=0)
      })

      orderlist.forEach((row)=>{
        let num1 = new Decimal(row.zeikomikakaku);
        let num2 = new Decimal(row.ordered);
        let num3 = new Decimal(row.tanka);
        //console_log(num1.mul(num2).toNumber());
        row.goukeikingaku = num1.mul(num2).toNumber()
        row.goukeitanka = num3.mul(num2).toNumber()
      })

      return orderlist
    })

    const shouhinMS_SALE = computed(()=>{
      return shouhinMS.value.filter((row)=>{
        return (row.status==='show')
      })
    })    
    watch(mode,()=>{//マスタ登録モードに合わせて商品名のリストを取得する
      if(mode.value==="new"){
        clear_ms()
        shouhinMS.value = []
        get_shouhinMS()
        get_shouhinMS_newcd()
      }else if(mode.value==="upd"){
        clear_ms()
        shouhinMS.value = []
        get_shouhinMS_online()
      }else{
        return
      }
    })

    watch(shouhinNM,()=>{//入力された商品名からマスタ情報を取得
      let shouhin = shouhinMS.value.filter((row)=>{
        return row.shouhinNM === shouhinNM.value
      })
      console_log(shouhin)
      if(shouhin.length!==0){
        tanka.value = shouhin[0].tanka
        status.value = shouhin[0].status
        zei.value = String(shouhin[0].zeikbn)
        info.value = shouhin[0].infomation
        customer_bikou.value = mode.value==="new"?customer_bikou.value:shouhin[0].customer_bikou
        midasi.value = shouhin[0].short_info
        pic_list.value=[]
        shouhinMS_pic.value.forEach((row)=>{
          if(row.shouhinCD===shouhin[0].shouhinCD){
            pic_list.value.push(row)
          }
        })
        console_log(pic_list.value)
        if(mode.value==="upd"){
          shouhinCD.value = shouhin[0].shouhinCD
        }
      }

      if(mode.value==="new"){
        get_shouhinMS_newcd()
      }else{
      }
    })

    let sort = 1
    const resort = (index) =>{//画像の並び順設定
      if(pic_list.value.length < sort){
        sort = 1
      }
      pic_list.value[index].sort = sort
      sort = Number(sort) + 1
    }

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
      params.append('shouhinCD',shouhinCD.value)

      axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="success"){
          pic_list.value = [...pic_list.value,...response.data.filename]
        }else{
        }
      })
      .catch((error)=>{
        console_log(error)
      })
      .finally(()=>{
        //loader.value = false
      })
    }
    const pic_delete = (filepass) =>{
      //アップされたファイルを削除
      //マスタに登録されたレコードを削除
      //pic_list[]からレコード削除
      const form = new FormData();
      form.append(`pic`, filepass)
      form.append(`csrf_token`, token)

      axios.post("ajax_file_delete.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="alert-success"){
          //画面のクリア
          pic_list.value.forEach((row,index)=>{
            if(row.filename===filepass){pic_list.value.splice(index,1)}
          })
          msg.value=`${filepass} を削除しました`

        }else{
          msg.value=`${filepass} の削除に失敗しました`
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        msg.value=`${filepass} の削除に失敗しました`
        token = response.data.csrf_create
      })
      .finally(()=>{
        //loader.value = false
      })

    }

    const ins_shouhinMS = ()=>{
      const form = new FormData();
      form.append(`shouhinCD`, shouhinCD.value)
      form.append(`shouhinNM`, shouhinNM.value)
      form.append(`status`, status.value)
      form.append(`tanka`, tanka.value)
      form.append(`zeikbn`, zei.value)
      form.append(`shouhizei`, shouhizei.value)
      form.append(`infomation`, info.value)
      form.append(`customer_bikou`, customer_bikou.value)
      form.append(`short_info`, midasi.value)
      form.append(`csrf_token`, token)
      let i = 0
      pic_list.value.forEach((row)=>{
        form.append(`user_file_name[${i}][sort]`,row.sort)
        form.append(`user_file_name[${i}][filename]`,row.filename)
        i=i+1
      })
      axios.post("ajax_delins_shouhinMS.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="alert-success"){
          //画面のクリア
          if(mode.value==="new"){
            get_shouhinMS()
            get_shouhinMS_newcd()
          }else if(mode.value==="upd"){
            get_shouhinMS_online()
          }
          msg.value=`${shouhinNM.value} を登録しました`
          clear_ms()
        }else{
          msg.value=`${shouhinNM.value} の登録に失敗しました`
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        msg.value=`${shouhinNM.value} の登録に失敗しました`
        token = response.data.csrf_create
      })
      .finally(()=>{
        //loader.value = false
      })
    }
    
    const clear_ms = () =>{
      console_log('clear_ms')
      shouhinNM.value = ''
      status.value = 'show'
      tanka.value = 0
      zei.value = 1101
      midasi.value = ''
      info.value = ''
      customer_bikou.value='ご要望等ございましたらご記入ください。'
      pic_list.value=[]
    }

    const order_kakaku = ref(0) //オーダー税込総額
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
    const order_count =(index,val) =>{
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
    }
    const ordered_count =(index,val) =>{
      let order = Number(get_ordered.value[index].ordered)
      if(order + Number(val) < 0){
        get_ordered.value[index].ordered = 0
      }else{
        let tanka = new Decimal(Number(get_ordered.value[index].tanka))
        let shouhizei = new Decimal(Number(get_ordered.value[index].shouhizei))
        let order_kin = new Decimal(order_kakaku.value)
        let zougen = new Decimal(val)
        order_kakaku.value = order_kin.add(zougen.mul(tanka.add(shouhizei))).toNumber()
        get_ordered.value[index].ordered = order + Number(val)
      }
      console_log(order_kakaku.value)
    }
    const btn_name = ref('ご注文内容確認')
    const ordering = () =>{
      if(mode.value==="shopping"){
        btn_name.value='戻る'
        mode.value="ordering"
      }else if(mode.value==="ordering"){
        btn_name.value='ご注文内容確認'
        mode.value="shopping"
      }

    }

    const shouhizei = computed(()=>{//消費税計算
      let zeiritu = 0.1
      zeiMS.forEach((row)=>{
        if(row.zeikbn===zei.value){
          zeiritu=row.ritu
        }
      })
      console_log(zeiritu)
      let num1 = new Decimal(tanka.value);
      let num2 = new Decimal(zeiritu);
      //console_log(num1.mul(num2).toNumber());
      return num1.mul(num2).toNumber()
    })

    const zeikomi = computed(()=>{//税込価格計算
      let zeiritu = 0.1 + 1
      zeiMS.forEach((row)=>{
        if(row.zeikbn===zei.value){
          zeiritu=Number(row.ritu) + Number(1)
        }
      })
      console_log(zeiritu)
      let num1 = new Decimal(tanka.value);
      let num2 = new Decimal(zeiritu);
      //console_log(num1.mul(num2).toNumber());
      return num1.mul(num2).toNumber()
    })

    watch(msg,()=>{
      console_log('watch msg => '+msg.value)
      setTimeout(()=>{msg.value=""}, 3000);//3s
      
    })
    const orderNO = ref('')
    const order_submit = () =>{
      let msg = ''

      if(od_atena.value==''){
        msg = ' 宛名'
      }
      if(od_yubin.value==''){
        msg = msg + ' 郵便番号'
      }
      if(od_jusho.value==''){
        msg = msg + ' 住所'
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
      const form = new FormData();
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
        //loader.value = false
      })

    }
    const order_clear =()=>{
      mode.value='shopping'
      get_ordered.value.forEach((row)=>{
        row.ordered=0
      })
      order_kakaku.value=0
    }
    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS"){
        get_shouhinMS()
        get_shouhinMS_newcd()
      }
      if(Where_to_use==="index"){
        get_shouhinMS_online()
        mode.value='shopping'
      }
    })

    return{
      msg,
      mode,
      shouhinMS,
      shouhinMS_pic,
      get_shouhinMS,
      shouhinCD,
      shouhinNM,
      status,
      tanka,
      zei,
      midasi,
      info,
      customer_bikou,
      pic_list,
      rez_shouhinCD,
      rez_shouhinNM,
      get_shouhinMS_online,
      input_file_btn,
      uploadfile,
      pic_delete,
      ins_shouhinMS,
      resort,
      shouhizei,
      zeikomi,
      order_count,
      ordered_count,
      order_kakaku,
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
      order_submit,
      orderNO,
      order_clear,
    }
  }
});


const admin_menu = (Where_to_use,p_token) => createApp({
  setup() {
    const menu = ref([
      {name:'サイト設定',url : 'configration.php'},
      {name:'販売商品編集',url : 'shouhinMS.php'},
      {name:'受注・発送・入金管理',url : 'order_management.php'},
    ])

    return{
      menu
    }
  }
})

const order_mng = (Where_to_use,p_token) => createApp({
  setup() {
    let token = p_token
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
    const set_order_sts = (orderNO,colum,val,index) =>{
      //colum項目にvalを設定
      console_log(orderNO)
      console_log(colum)
      console_log(orderlist_hd.value[index])

      const form = new FormData();
      form.append(`orderNO`, orderNO)
      form.append(`colum`, colum)
      form.append(`value`, val)
      form.append(`csrf_token`, token)

      axios.post("ajax_upd_order_h.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="alert-success"){
        }else{
          alert('更新失敗')
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        token = response.data.csrf_create
      })
      .finally(()=>{
        //loader.value = false
      })

    }
    const unlock = (id)=>{
      document.getElementById(id).disabled = false
      document.getElementById(id).readOnly = false
    }

    const yagou = ref('')
    const site_name = ref('')
    const tantou = ref('')
    const shacho = ref('')
    const jusho = ref('')
    const tel = ref('')
    const mail = ref('')
    const mail_body = ref('')

    const mail_body_template = computed(()=>{
      let val=mail_body.value
      let return_value=[]
      let orders = '【ご注文内容】\n'
      
      orderlist_hd.value.forEach((row)=>{
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
        orders = orders + "御請求額： " + String(Number(row.税込総額).toLocaleString()) + " 円"
        
        val = mail_body.value
        
        val = val.replace(/<購入者名>/g,row.name)
        val = val.replace(/<注文内容>/g,orders)
        val = val.replace(/<購入者情報>/g,'【ご注文主】\nお名前：' + row.name + '\n郵便番号：' + row.yubin + '\n住所：' + row.jusho + '\nTEL：' + row.tel + '\nMAIL：' + row.mail + '\nオーダー備考：\n' + row.bikou + '')
        val = val.replace(/<届け先情報>/g,'【お届け先】\nお名前：' + row.st_name + '\n郵便番号：' + row.st_yubin + '\n送付先住所：' + row.st_jusho + '\nTEL：' + row.st_tel + '')
        val = val.replace(/<自社名>/g,yagou.value)
        val = val.replace(/<自社住所>/g,jusho.value)
        val = val.replace(/<問合せ受付TEL>/g,tel.value)
        val = val.replace(/<問合せ受付MAIL>/g,mail.value)
        val = val.replace(/<問合担当者>/g,tantou.value)
        val = val.replace(/<代表者>/g,shacho.value)
        
        return_value.push({orderNO:row.orderNO,mailbody:val})
      })
      
      return return_value
    })

    const approval_email = (orderNO,index) =>{
      if(confirm('お客様に受付完了のメールを送付しますか？')){
      }else{
        return
      }
      console_log(orderNO)
      console_log(orderlist_hd.value[index].orderNO)
      console_log(mail_body_template.value[index].orderNO)
      //念のための確認
      if(orderNO!==orderlist_hd.value[index].orderNO){
        alert('受付番号不一致')
        return
      }
      if(orderNO!==mail_body_template.value[index].orderNO){
        alert('受付番号不一致')
        return
      }

      const form = new FormData();
      form.append(`mailto`, orderlist_hd.value[index].mail)
      form.append(`subject`, `【${site_name.value}】ご注文についてのご連絡「受付番号：${orderNO}」`)
      form.append(`body`, mail_body_template.value[index].mailbody)
    }

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS"){
      }
      get_orderlist()
      GET_USER2()
      .then((response)=>{
        console_log('owata')
        console_log(response)
        yagou.value = response.yagou
        site_name.value = response.site_name
        tantou.value = response.name
        shacho.value = response.shacho
        jusho.value = response.jusho
        tel.value = response.tel
        mail.value = response.mail
        mail_body.value = response.mail_body

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
      mail_body_template,
      approval_email,
    }
  }
})

const configration = (Where_to_use,p_token) => createApp({
  setup() {
    let token = p_token
    const yagou = ref('')
    const tantou = ref('')
    const shacho = ref('')
    const jusho = ref('')
    const tel = ref('')
    const mail = ref('')
    const mail_body = ref('<購入者名>様\n\nご注文ありがとうございます。\n以下の内容にて、ご注文を承りました。\n\n<注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\n下記支払先へのお支払いが確認できましたら発送準備に入ります。\n【銀行振込】\n〇〇銀行〇〇支店　普通　0123456\n振込手数料についてはお客様負担となります\n\n【paypay】\n＊＊＊＊＊＊\n\n不明点・お問い合わせ等ございましたら下記へご連絡ください。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const site_name = ref('')
    const logo = ref('')
    const get_user = () =>{
      axios
      .get(`ajax_get_usersMSonline.php`)
      .then((response) => {
        yagou.value = response.data[0].yagou
        tantou.value = response.data[0].name
        shacho.value = response.data[0].shacho
        jusho.value = response.data[0].jusho
        tel.value = response.data[0].tel
        mail.value = response.data[0].mail
        if(response.data[0].mail_body!==""){
          mail_body.value = response.data[0].mail_body
        }
        site_name.value = response.data[0].site_name
        logo.value = response.data[0].logo
        console_log('ajax_get_usersMSonline succsess')
      })
      .catch((error)=>{
        console_log('ajax_get_usersMSonline.php ERROR')
        console_log(error)
      })
      .finally(()=>{
        //loader.value = false
      })
    }

    const set_user = () =>{
      const form = new FormData();
      form.append(`yagou`, yagou.value)
      form.append(`name`, name.value)
      form.append(`shacho`, shacho.value)
      form.append(`jusho`, jusho.value)
      form.append(`tel`, tel.value)
      form.append(`mail`, mail.value)
      form.append(`mail_body`, mail_body.value)
      form.append(`site_name`, site_name.value)
      form.append(`logo`, logo.value)
      form.append(`csrf_token`, token)

      axios.post("ajax_delins_userMSonline.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="alert-success"){
        }else{
          alert('更新失敗')
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        token = response.data.csrf_create
      })
      .finally(()=>{
        //loader.value = false
      })

    }

    const mail_body_sample = computed(()=>{
      let val=mail_body.value

      val = val.replace(/<購入者名>/g,'田中次郎')
      val = val.replace(/<注文内容>/g,'【ご注文内容】\n◆iPad\n価格( 10,909 円) x 1(コ) = 合計 10,909 円(税抜)\n\n備考：ご要望等ございましたらご記入ください。\n\nご注文総額：￥11,999  内税(1,090)')
      val = val.replace(/<購入者情報>/g,'【ご注文主】\nお名前：田中次郎\n郵便番号：261XXXX\n送付先住所：千葉市美浜区〇〇〇\nTEL：09012341234\nMAIL：sample@gmail.com\nオーダー備考：\nご要望等ございましたらご記入ください。')
      val = val.replace(/<届け先情報>/g,'【お届け先】\nお名前：佐藤次郎\n郵便番号：261XXXX\n送付先住所：千葉市若葉区〇〇〇\nTEL：09012341234')
      val = val.replace(/<自社名>/g,'サンプル株式会社')
      val = val.replace(/<自社住所>/g,'千葉県千葉市稲毛区〇〇〇〇')
      val = val.replace(/<問合せ受付TEL>/g,'0120-00-0000')
      val = val.replace(/<問合せ受付MAIL>/g,'sample@gmail.com')
      val = val.replace(/<問合担当者>/g,'小泉純一郎')
      val = val.replace(/<代表者>/g,'田中角栄')
      
      return val
    })

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS"){
      }
      get_user()
    })

    return{
      mail_body_sample,
      yagou,
      tantou,
      shacho,
      jusho,
      tel,
      mail,
      mail_body,
      site_name,
      logo,
      set_user,
    }
  }
})

//グローバル関数
const GET_USER2 = ()=>{
	return new Promise((resolve, reject) => {
		GET_USER_SHORI(resolve);
	});
}
const GET_USER_SHORI = (resolve) =>{
  let obj
  axios
  .get(`ajax_get_usersMSonline.php`)
  .then((response) => {
    obj = response.data[0]
    console_log('ajax_get_usersMSonline succsess')
  })
  .catch((error)=>{
    console_log('ajax_get_usersMSonline.php ERROR')
    console_log(error)
  })
  .finally(()=>{
    //loader.value = false
    resolve(obj)
    //return obj
  })
}
