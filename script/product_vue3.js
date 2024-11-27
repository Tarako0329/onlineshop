//const { createApp, ref, onMounted, onBeforeMount, computed,watch,unheadvue } = Vue;
import { createApp, ref, onMounted, onBeforeMount,computed,watch } from 'vue';
//import { createHead,useHead   } from 'unheadvue';
//createHead()

//export const product_page = (Where_to_use,p_token,p_shouhin_cd,p_shop_id,p_site_name) => createApp({//販売画面
export const product_page = (Where_to_use,p_token,p_shouhin_cd,p_site_name) => createApp({//販売画面
  setup() {
    
    let token = p_token
    const msg=ref('')
    const loader = ref(false)

    const shouhinMS = ref([])
    //const shouhinMS = ref(p_shouhin_info)
    const shouhinMS_pic = ref([])
    //const shouhinMS_pic = ref(p_shouhin_pic)
    
    const mode = ref('new')

    const get_shouhinMS_online = (product) => {//商品マスタ取得
      axios
      .get(`ajax_get_shouhinMS_online.php?f=%`)
      .then((response) => {
        if(response.data.alert==="success"){
          shouhinMS.value = [...response.data.dataset]
          shouhinMS_pic.value = [...response.data.pic_set]
          console_log('get_shouhinMS_online succsess')
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

    const order_kakaku = ref(0) //オーダー税込総額
    const order_shop_id = ref('')
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
      IDD_Write(tableNM,[{
        id:p_shouhin_cd
        ,shop_id:shouhinMS.value[index].uid
        ,shouhinCD:shouhinMS.value[index].shouhinCD
        //,shouhinMS_index:index
        ,ordered:shouhinMS.value[index].ordered
      }])
    }
    const btn_name = ref('カート')
    const ordering = (uid) =>{
      console_log(`ordering:${uid}`)
      sessionStorage.setItem('from','product_cart');
      sessionStorage.setItem('from_uid',uid);
      window.location.href = './'
    }

    watch(msg,()=>{
      console_log('watch msg => '+msg.value)
      setTimeout(()=>{msg.value=""}, 3000);//3s
      
    })

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

    const qa_index = ref(0)
    const qa_yagou = ref('')
    const qa_shouhinNM = ref('')
    const qa_name = ref('')
    const qa_mail = ref('')
    const qa_text = ref('')
    const qa_head = ref('')
    const set_qa_index = (p_index) => {
      qa_index.value = p_index
      qa_yagou.value = shouhinMS.value[p_index].yagou
      qa_shouhinNM.value = shouhinMS.value[p_index].shouhinNM
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
      form.append(`mailtoBCC`, shouhinMS.value[qa_index.value].mail)
      form.append(`lineid`, shouhinMS.value[qa_index.value].line_id)
      form.append(`shop_id`, shouhinMS.value[qa_index.value].uid)
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
      //get_shouhinMS_online,
      order_count,
      Charge_amount_by_store,
      order_shop_id,
      btn_name,
      ordering,
      get_ordered,
      shouhinMS,
      order_clear,
      img_zoom,
      pic_zoom,
      shouhinMS_pic,//写真拡大
      qa_index,
      qa_yagou,
      qa_shouhinNM,
      qa_mail,
      qa_name,
      qa_head,
      qa_text,
      set_qa_index,
      send_email,

    }
  }
});