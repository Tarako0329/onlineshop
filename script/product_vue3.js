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
          
          shouhinMS.value.forEach((list,index)=>{//SEOmetaタグ関連の設定
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
    }
  }
});