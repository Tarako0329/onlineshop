//const { createApp, ref, onMounted, onBeforeMount, computed,watch,unheadvue } = Vue;
import { createApp, ref, onMounted, onBeforeMount,computed,watch } from 'vue';
import { createHead,useHead   } from 'unheadvue';
createHead()

//export const product_page = (Where_to_use,p_token,p_shouhin_cd,p_shop_id,p_site_name) => createApp({//販売画面
export const product_page = (Where_to_use,p_token,p_shouhin_cd,p_site_name) => createApp({//販売画面
  setup() {
    
    let token = p_token
    const msg=ref('')
    const loader = ref(false)

    const shouhinMS = ref([])
    const shouhinMS_pic = ref([])
    
    const mode = ref('new')

    const get_shouhinMS_online = (product) => {//商品マスタ取得
      axios
      .get(`ajax_get_shouhinMS_online.php?f=%`)
      //.get(`ajax_get_shouhinMS_online_pinpoint.php?p=${product}`)
      .then((response) => {
        if(response.data.alert==="success"){
          shouhinMS.value = [...response.data.dataset]
          shouhinMS_pic.value = [...response.data.pic_set]
          console_log('get_shouhinMS_online succsess')
          
          shouhinMS.value.forEach((list,index)=>{
            if(list.uid + "-" + list.shouhinCD === p_shouhin_cd){
              console_log(`みつけた！${list.shouhinNM}`)
              useHead({
                title: `${list.shouhinNM} - 通販サイト『${p_site_name} of ${list.site_name}』`,
                meta: [
                  { name: "description", content: `${list.short_info}` }
                  ,{ property: "og:title", content: `${list.shouhinNM} - 通販サイト『${p_site_name} of ${list.site_name}』` }
                  ,{ property: "og:description", content: `${list.short_info}` }
                  ,{ property: "og:url", content: `https://cafe-present.greeen-sys.com/product.php?id=${p_shouhin_cd}` }
                  ,{ property: "og:type", content: `website` }
                  ,{ property: "og:site_name", content: `通販サイト『${p_site_name}』` }
                  ,{ property: "og:image", content: `https://cafe-present.greeen-sys.com/${shouhinMS_pic.value[0].filename}`}
                ],
              });
            }
          })
          /*
          useHead({
            title: `${shouhinMS.value[0].shouhinNM} - 通販サイト『${p_site_name} of ${shouhinMS.value[0].site_name}』`,
            meta: [
              { name: "description", content: `${shouhinMS.value[0].short_info}` }
              ,{ property: "og:title", content: `${shouhinMS.value[0].shouhinNM} - 通販サイト『${p_site_name} of ${shouhinMS.value[0].site_name}』` }
              ,{ property: "og:description", content: `${shouhinMS.value[0].short_info}` }
              ,{ property: "og:url", content: `https://cafe-present.greeen-sys.com/product.php?id=${p_shouhin_cd}` }
              ,{ property: "og:type", content: `website` }
              ,{ property: "og:site_name", content: `通販サイト『${p_site_name}』` }
              ,{ property: "og:image", content: `https://cafe-present.greeen-sys.com/${shouhinMS_pic.value[0].filename}`}
            ],
          });
          */
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

    //const search_word = ref('')
    //const serch_type = ref('商品名＋説明文') //or 商品名
    /*const shouhinMS_SALE = computed(()=>{
      return shouhinMS.value.filter((row)=>{
        return (row.status==='show' && (row.uid + row.shouhinCD) == p_shouhin_cd)
      })
    })*/
 
    const order_kakaku = ref(0) //オーダー税込総額
    const order_shop_id = ref('')
    /*
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
    */
    const order_count =(index,val) =>{//注文画面の数量増減ボタン
      let order = Number(shouhinMS.value[index].ordered)
      if(order + Number(val) < 0){
        shouhinMS.value[index].ordered = 0
      }else{
        shouhinMS.value[index].ordered = order + Number(val)
        let tanka = new Decimal(Number(shouhinMS.value[index].tanka))
        let shouhizei = new Decimal(Number(shouhinMS.value[index].shouhizei))
        let order_kin = new Decimal(order_kakaku.value)
        let zougen = new Decimal(val)
        order_kakaku.value = order_kin.add(zougen.mul(tanka.add(shouhizei))).toNumber()
      }
      console_log(order_kakaku.value)
      IDD_Write('cart',[{
        id:p_shouhin_cd
        ,shop_id:shouhinMS.value[index].uid
        ,shouhinCD:shouhinMS.value[index].shouhinCD
        ,shouhinMS_index:index
        ,ordered:shouhinMS.value[index].ordered
      }])
    }
    /*
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
    */
    const btn_name = ref('カート')
    const ordering = (uid) =>{
      console_log(`ordering:${uid}`)
      /*
      if(mode.value==="shopping"){
        order_shop_id.value = uid
        btn_name.value='戻る'
        mode.value="ordering"
      }else if(mode.value==="ordering"){
        order_shop_id.value = ''
        btn_name.value='カート'
        mode.value="shopping"
      }
      */
      //IDD_Write('cart',[{id:p_shouhin_cd,shop_id:shouhinMS.value[0].uid,shouhinCD:shouhinMS.value[0].shouhinCD,ordered:shouhinMS.value[0].ordered}])
    }

    watch(msg,()=>{
      console_log('watch msg => '+msg.value)
      setTimeout(()=>{msg.value=""}, 3000);//3s
      
    })
/*
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
*/
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
    /*
    const shouhinMS_pic_sel = computed(()=>{
      const rtn = shouhinMS_pic.value.filter((row)=>{
        if(row.shouhinCD===pic_zoom_cd.value && row.uid===pic_zoom_uid.value){return true}
      })
      return rtn
    })
    */
    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      
      get_shouhinMS_online(p_shouhin_cd)
      mode.value='shopping'
      document.getElementById("menu_home").classList.add("active");
      
    })

    return{
      msg,
      loader,
      mode,
      shouhinMS_pic,
      get_shouhinMS_online,
      order_count,
      //ordered_count,
      //order_kakaku,
      Charge_amount_by_store,
      order_shop_id,
      /*
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
      */
      btn_name,
      ordering,
      get_ordered,
      //shouhinMS_SALE,
      shouhinMS,
      //order_submit,
      //orderNO,
      order_clear,
      img_zoom,
      pic_zoom,
      //shouhinMS_pic_sel,//写真拡大
      shouhinMS_pic,//写真拡大
      //search_word,
      //serch_type,
    }
  }
});