const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const shouhinMS = (Where_to_use,p_token) => createApp({//商品マスタ管理
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
    const loader = ref(false)

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
          //console_log(response.data.pic_set)
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
    /*
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
    */
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
      loader.value = true
      axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="success"){
          pic_list.value = [...pic_list.value,...response.data.filename]
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
      let msg = ''
      if(shouhinNM.value == ''){
        msg = msg + ' 商品名'
      }
      if(tanka.value == ''){
        msg = msg + ' 単価'
      }
      if(midasi.value == ''|| midasi.value == null){
        msg = msg + ' 商品説明（見出し）'
      }
      if(info.value == '' || info.value == null){
        msg = msg + ' 商品説明（詳細）'
      }
      if(pic_list.value.length === 0){
        msg = msg + ' 写真'
      }
      if(msg.length != 0){
        alert(`${msg} を設定してください。`)
        return
      }
      loader.value = true
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
          //msg.value=`${shouhinNM.value} を登録しました`
          alert(`${shouhinNM.value} を登録しました`)
          clear_ms()
        }else{
          //msg.value=`${shouhinNM.value} の登録に失敗しました`
          alert(`${shouhinNM.value} の登録に失敗しました`)
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        //msg.value=`${shouhinNM.value} の登録に失敗しました`
        alert(`${shouhinNM.value} の登録に失敗しました`)
        token = response.data.csrf_create
      })
      .finally(()=>{
        loader.value = false
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
    /*
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
    */
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
    /*
    const orderNO = ref('')
    const order_submit = () =>{
      let msg = ''
      loader.value = true
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
      loader.value = true

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
        loader.value = false
      })

    }
    const order_clear =()=>{
      mode.value='shopping'
      get_ordered.value.forEach((row)=>{
        row.ordered=0
      })
      order_kakaku.value=0
    }
    
    const img_zoom =ref(false)
    const pic_zoom_cd = ref(0)
    const pic_zoom = (shouhinCD) =>{
      console_log(`pic_zoom:${shouhinCD}`)
      pic_zoom_cd.value = Number(shouhinCD)
      if(img_zoom.value){
        img_zoom.value = false
      }else{
        img_zoom.value = true
      }
      console_log(`pic_zoom:${pic_zoom_cd}`)
    }
    const shouhinMS_pic_sel = computed(()=>{
      const rtn = shouhinMS_pic.value.filter((row)=>{
        if(row.shouhinCD===pic_zoom_cd.value){return true}
      })
      return rtn
    })
    */
    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS.php"){
        get_shouhinMS()
        get_shouhinMS_newcd()
      }
    })

    return{
      msg,
      loader,
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
      /*
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
      img_zoom,
      pic_zoom,
      shouhinMS_pic_sel,
      */
    }
  }
});

const sales = (Where_to_use,p_token) => createApp({//販売画面
  setup() {
    /*
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
    */
    let token = p_token
    const msg=ref('')
    const loader = ref(false)

    const shouhinMS = ref([])
    const shouhinMS_pic = ref([])
    
    const mode = ref('new')
    /*
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
    */
    /*
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
    */
   
    /*
    const shouhinNM = ref('')
   
    const shouhinCD = ref('')
    const status = ref('show')
    const tanka = ref(0)
    const zei = ref(1101)
    const midasi = ref('')
    const info = ref('')
    const customer_bikou = ref('ご要望等ございましたらご記入ください。')
    const pic_list = ref([])
    const rez_shouhinCD = ref('')
    const rez_shouhinNM = ref('')
    */
    const get_shouhinMS_online = (serch) => {//商品マスタ取得
      axios
      .get(`ajax_get_shouhinMS_online.php?f=${serch}`)
      .then((response) => {
        if(response.data.alert==="success"){
          shouhinMS.value = [...response.data.dataset]
          shouhinMS_pic.value = [...response.data.pic_set]
          console_log('get_shouhinMS_online succsess')
          //console_log(response.data.pic_set)
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
            ,"tel":row.tel
            ,"mail":row.mail
            ,"cancel_rule":row.cancel_rule
            ,"seikyu":kingaku_temp.toNumber()
          })
          
          kingaku_temp = Decimal(0)
        }
        /*else if(index !== (shouhinMS.value.length - 1) && row.uid !== shouhinMS.value[index+1].uid && kingaku_temp.toNumber() !== 0){
          kingakus.push({
            "uid":row.uid
            ,"yagou":row.yagou
            ,"seikyu":kingaku_temp.toNumber()
          })
          kingaku_temp = Decimal(0)
        }*/
      })

      if(kingakus.length===0){
        kingakus.push({
          "uid":9999999
          ,"yagou":'shop'
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

    const shouhinMS_SALE = computed(()=>{
      return shouhinMS.value.filter((row)=>{
        return (row.status==='show')
      })
    })    
    /*
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
    */
/*
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
      loader.value = true
      axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="success"){
          pic_list.value = [...pic_list.value,...response.data.filename]
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
      loader.value = true
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
        loader.value = false
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
    */
   

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
        get_ordered.value[index].ordered = order + Number(val)
      }
      console_log(order_kakaku.value)
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
    /*
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
    */
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
        if(row.uid === order_shop_id.value){row.ordered=0}
      })
      order_shop_id.value = ''
      btn_name.value='カート'
      //order_kakaku.value=0
    }

    const img_zoom =ref(false)
    const pic_zoom_cd = ref(0)
    const pic_zoom = (shouhinCD) =>{
      console_log(`pic_zoom:${shouhinCD}`)
      pic_zoom_cd.value = Number(shouhinCD)
      if(img_zoom.value){
        img_zoom.value = false
      }else{
        img_zoom.value = true
      }
      console_log(`pic_zoom:${pic_zoom_cd}`)
    }
    const shouhinMS_pic_sel = computed(()=>{
      const rtn = shouhinMS_pic.value.filter((row)=>{
        if(row.shouhinCD===pic_zoom_cd.value){return true}
      })
      return rtn
    })

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)

      if(Where_to_use==="index"){
        get_shouhinMS_online()
        mode.value='shopping'
        document.getElementById("menu_home").classList.add("active");
      }
    })

    return{
      msg,
      loader,
      mode,
      //shouhinMS,
      shouhinMS_pic,
      //get_shouhinMS,
      /*
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
      */
      get_shouhinMS_online,
      //input_file_btn,
      //uploadfile,
      //pic_delete,
      //ins_shouhinMS,
      //resort,
      //shouhizei,
      //zeikomi,
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
      order_submit,
      orderNO,
      order_clear,
      img_zoom,
      pic_zoom,
      shouhinMS_pic_sel,//写真拡大
    }
  }
});

const admin_menu = (Where_to_use,p_token,user_hash) => createApp({//管理者メニュー
  setup() {
    const menu = ref([
      {name:'サイト設定',url : `configration.php?key=${user_hash}`},
      {name:'商品管理',url : `shouhinMS.php?key=${user_hash}`},
      {name:'受注管理',url : `order_management.php?key=${user_hash}`},
      {name:'発送サポート',url : `Unshipped_slip.php?key=${user_hash}`},
    ])

    onMounted(()=>{
      console_log(`onMounted admin_menu: ${Where_to_use}`)
      if(Where_to_use==="configration.php"){
        document.getElementById("menu_00").classList.add("active");
      }else if(Where_to_use==="shouhinMS.php"){
        document.getElementById("menu_01").classList.add("active");
      }else if(Where_to_use==="order_management.php"){
        document.getElementById("menu_02").classList.add("active");
      }else if(Where_to_use==="Unshipped_slip.php"){
        document.getElementById("menu_03").classList.add("active");
      }else{
      }
    })

    return{
      menu,
    }
  }
})

const order_mng = (Where_to_use,p_token) => createApp({//販売管理
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
    const set_order_sts = (orderNO,colum,val,index) =>{//受注情報の更新
      //colum項目にvalを設定
      console_log(orderNO)
      console_log(colum)
      console_log(orderlist_hd.value[index])
      if(colum==="cancel" && val===1){
        if(confirm("ご注文をキャンセルしてよいですか？")===false){
          return
        }
      }

      const form = new FormData();
      form.append(`orderNO`, orderNO)
      form.append(`colum`, colum)
      form.append(`value`, val)
      form.append(`csrf_token`, token)

      axios.post("ajax_upd_order_h.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
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
                send_mailbody.value=get_mail_sample(mail_body_sent.value,index)
                document.getElementById("modalon").click()
              }
              if(colum==="first_answer" && val===1){
                send_mailbody.value=get_mail_sample(mail_body.value,index)
                document.getElementById("modalon").click()
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
      
      orders_postage = orders + "\n\n◆送料：" + String(Number(row.postage).toLocaleString()) + " 円\n\n御請求額："+String((Number(row.税込総額)+Number(row.postage)).toLocaleString())+"円"
      
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
      form.append(`mailbody`, send_mailbody.value)
      form.append(`csrf_token`, token)

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
        console_log('owata')
        console_log(response)
        yagou.value = response[0].yagou
        site_name.value = response[0].site_name
        tantou.value = response[0].name
        shacho.value = response[0].shacho
        jusho.value = response[0].jusho
        tel.value = response[0].tel
        mail.value = response[0].mail
        cc_mail.value = response[0].cc_mail
        mail_body.value = response[0].mail_body
        mail_body_paid.value = response[0].mail_body_paid
        mail_body_sent.value = response[0].mail_body_sent
        chk_recept.value = response[0].chk_recept===1?true:false
        chk_sent.value = response[0].chk_sent===1?true:false
        chk_paid.value = response[0].chk_paid===1?true:false
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

const configration = (Where_to_use,p_token) => createApp({//サイト設定
  setup() {
    let token = p_token
    const yagou = ref('')
    const tantou = ref('')
    const shacho = ref('')
    const jusho = ref('')
    const tel = ref('')
    const mail = ref('')
    const cc_mail = ref('')
    const mail_body_auto = ref('<購入者名> 様\n\nご注文ありがとうございます。\n以下の内容にて、ご注文を受け付けました。\n\n<注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\n※弊社担当にてご注文内容の確認が取れましたら、お支払い・納期等についてのご案内メールを送付いたします。\n※メールが届かない場合、また、不明点・お問い合わせ等ございましたら以下までご連絡くださいませ。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body = ref('<購入者名> 様\n\nご注文ありがとうございます。\n以下の内容にて、ご注文を承りました。\n\n<送料込の注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\n下記支払先へのお支払いが確認できましたら発送準備に入ります。\n【銀行振込】\n〇〇銀行〇〇支店　普通　0123456\n振込手数料についてはお客様負担となります\n\n【paypay】\n＊＊＊＊＊＊\n\n不明点・お問い合わせ等ございましたら下記へご連絡ください。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body_paid = ref('<購入者名> 様\n\nいつもありがとうございます。\n\n以下のご注文についてのお支払いを確認いたしました。\n発送が終わりましたら再度ご連絡させていただきます。\n\n<送料込の注文内容>\n\n<購入者情報>\n\n何かございましたら以下までご連絡くださいませ。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
    const mail_body_sent = ref('<購入者名> 様\n\nいつもありがとうございます。\n\n以下のご注文について、本日商品を発送いたしました。\n\n<注文内容>\n\n<購入者情報>\n\n<届け先情報>\n\n（お届け先未記載の場合は<購入者名> 様宛にお送りしてます。）\n\n配送状況などについては下記URLよりご確認いただけます。\n\nhttps://sagawa.com（サガワなどのURL貼付け）\n\n商品のご到着までしばらくお待ちください。\n\n今後とも <自社名> をよろしくお願いします。\n\n*************************\n<自社名>\n<自社住所>\nTEL:<問合せ受付TEL>\nMAIL:<問合せ受付MAIL>\n*************************')
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

    const set_user = () =>{
      loader.value = true
      const form = new FormData();
      form.append(`yagou`, yagou.value)
      form.append(`name`, tantou.value)
      form.append(`shacho`, shacho.value)
      form.append(`jusho`, jusho.value)
      form.append(`tel`, tel.value)
      form.append(`mail`, mail.value)
      form.append(`cc_mail`, cc_mail.value)
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
      form.append(`csrf_token`, token)

      axios.post("ajax_delins_userMSonline.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="alert-success"){
          loader.value = false
          alert('更新しました')
        }else{
          alert('更新失敗')
        }
        token = response.data.csrf_create
      })
      .catch((error,response)=>{
        console_log(error)
        token = response.data.csrf_create
        alert('更新error')
      })
      .finally(()=>{
        loader.value = false
      })

    }

    const get_mail_sample = (template) =>{
      let val=template

      val = val.replace(/<購入者名>/g,'田中次郎')
      val = val.replace(/<注文内容>/g,'【ご注文内容】\n◆商品Ａ\n価格( 10,000 円) x 2(コ) = 合計 20,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆商品Ｂ\n価格( 5,000 円) x 1(コ) = 合計 5,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆消費税：10% = 2,500 円\n◆ご注文総額：27,500円')
      val = val.replace(/<送料込の注文内容>/g,'【ご注文内容】\n◆商品Ａ\n価格( 10,000 円) x 2(コ) = 合計 20,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆商品Ｂ\n価格( 5,000 円) x 1(コ) = 合計 5,000 円(税抜)\n備考：ご要望等ございましたらご記入ください。\n\n◆消費税：10% = 2,500 円\n◆ご注文総額：27,500円\n\n◆配送料：500円\n\n御請求額：￥28,000')
      val = val.replace(/<購入者情報>/g,'【ご購入者】\nお名前：田中次郎\n郵便番号：261XXXX\n送付先住所：千葉市美浜区〇〇〇\nTEL：09012341234\nMAIL：sample@gmail.com\nオーダー備考：\nご要望等ございましたらご記入ください。')
      val = val.replace(/<届け先情報>/g,'【お届け先】\nお名前：佐藤次郎\n郵便番号：261XXXX\n送付先住所：千葉市若葉区〇〇〇\nTEL：09012341234')
      val = val.replace(/<自社名>/g,'サンプル株式会社')
      val = val.replace(/<自社住所>/g,'千葉県千葉市稲毛区〇〇〇〇')
      val = val.replace(/<問合せ受付TEL>/g,'0120-00-0000')
      val = val.replace(/<問合せ受付MAIL>/g,'sample@gmail.com')
      val = val.replace(/<問合担当者>/g,'小泉純一郎')
      val = val.replace(/<代表者>/g,'田中角栄')

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

    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS"){
      }

      GET_USER2()
      .then((response)=>{
        yagou.value = response[0].yagou
        site_name.value = response[0].site_name
        site_pr.value = response[0].site_pr
        logo.value = response[0].logo
        tantou.value = response[0].name
        shacho.value = response[0].shacho
        jusho.value = response[0].jusho
        tel.value = response[0].tel
        mail.value = response[0].mail
        cc_mail.value = response[0].cc_mail
        chk_recept.value = response[0].chk_recept===1?true:false
        chk_sent.value = response[0].chk_sent===1?true:false
        chk_paid.value = response[0].chk_paid===1?true:false
        lock_sts.value = response[0].lock_sts
        if(response[0].mail_body!=="''"){
          mail_body.value = response[0].mail_body
        }
        if(response[0].mail_body_auto!=="''"){
          mail_body_auto.value = response[0].mail_body_auto
        }
        if(response[0].mail_body_paid!=="''"){
          mail_body_paid.value = response[0].mail_body_paid
        }
        if(response[0].mail_body_sent!=="''"){
          mail_body_sent.value = response[0].mail_body_sent
        }
        if(response[0].mail_body_cancel!=="''"){
          mail_body_cancel.value = response[0].mail_body_cancel
        }
        if(response[0].cancel_rule!=="''"){
          cancel_rule.value = response[0].cancel_rule
        }

      })
    })

    return{
      yagou,
      tantou,
      shacho,
      jusho,
      tel,
      mail,
      cc_mail,
      site_name,
      site_pr,
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
    }
  }
})


const Unsipped_slip = (Where_to_use,p_token) => createApp({//販売管理
  setup() {
    const Unsippedlist = ref([])
    const Unsippedlist_uchiwake = ref([])
    const FROM = ref('')
    const TO = ref(new Date().toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit",day: "2-digit"}).replaceAll('/', '-'))

    const get_unsipped_list = () => {
      axios
      .get(`ajax_get_unsipped.php?from=${FROM.value}&to=${TO.value}`)
      .then((response) => {
        console_log(response)
        Unsippedlist.value = response.data.result
        Unsippedlist_uchiwake.value = response.data.result2
        console_log('ajax_get_unsipped succsess')
      })
      .catch((error)=>{
        console_log('ajax_get_unsipped.php ERROR')
        console_log(error)
      })
      .finally(()=>{
      })
    }
    
    const pdf_url = computed(()=>{return `Unshipped_slip_pdf.php?from=${FROM.value}&to=${TO.value}`})

    watch(FROM,()=>{
      console_log('watch FROM => '+FROM.value)
      get_unsipped_list()
    })
    watch(TO,()=>{
      console_log('watch TO => '+TO.value)
      get_unsipped_list()
    })

    onMounted(()=>{
      console_log(`onMounted:${Where_to_use}`)
      get_unsipped_list()
    })

    return{
      Unsippedlist,
      Unsippedlist_uchiwake,
      get_unsipped_list,
      FROM,
      TO,
      pdf_url,
    }
  }
})


const shops = (Where_to_use,p_token) => createApp({//サイト設定
  setup() {
    const shoplist = ref([])

    onMounted(()=>{
      GET_USER2()
      .then((response)=>{
        shoplist.value = response
      })
    })

    return {
      shoplist,
    }
  }
})




const GET_ORDER_LIST = ()=>{//サイト設定情報取得
	return new Promise((resolve, reject) => {
		GET_USER_SHORI(resolve);
	});
}
const GET_ORDER_LIST_SHORI = (resolve) =>{
  let obj
  axios
  .get(`ajax_get_usersMSonline.php`)
  .then((response) => {
    obj = response.data
    console_log('ajax_get_usersMSonline succsess')
  })
  .catch((error)=>{
    console_log('ajax_get_usersMSonline.php ERROR')
    console_log(error)
  })
  .finally(()=>{
    resolve(obj)
  })
}
