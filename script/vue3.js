const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const dataset = (Where_to_use) => createApp({
  setup() {
    const pagename = ref(Where_to_use)

    const input_file_btn = () =>{
      document.getElementById('file').click()
    }
    
    const read_html_moneyforward = () => {//
      axios
      .get(`ajax_read_forward.php?fn=${readfilename.value}`)
      .then((response) => {
        console_log(response.data)
        console_log('read_html_moneyforward succsess')
      })
      .catch((error)=>{
        console_log('read_html_moneyforward.php ERROR')
        console_log(error)
        alert('リターンエラー：登録できませんでした')
      })
      .finally(()=>{
        loader.value = false
      })
   }

    const uploadfile = () =>{
      const file = document.getElementById('file').files[0];
      console_log(file.name)
      const params = new FormData();
      params.append('user_file_name', file);
      axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        //console_log(response.data)
        if(response.data.status==="success"){
          readfilename.value = response.data.filename
          read_html_moneyforward()
        }else{

        }
      })
      .catch((error)=>{
        console_log(error)
      })
      .finally(()=>{
        loader.value = false
      })
    }
    
    const readdata_filter = computed(() => {
    })


    watch(readdata_filter,()=>{//明細のフィルタリングに合わせて、フィルタ選択肢を絞る
      console_log('watch readdata_filter')
    })



    onMounted(()=>{
      console_log("onMounted")
      
      if(pagename.value!=="data_custmer.php"){
      }
    })
    onBeforeMount(()=>{
      console_log("onBeforeMount:"+pagename.value)
      if(pagename.value==="data_summary12m.php"){
      }
    })


    return{
    }
  }
});

const shouhinMS = (Where_to_use) => createApp({
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

    watch(mode,()=>{//マスタ登録モードに合わせて商品名のリストを取得する
      clear_ms()
      shouhinMS.value = []
      if(mode.value==="new"){
        get_shouhinMS()
        //get_shouhinMS_online()
        get_shouhinMS_newcd()
      }else if(mode.value==="upd"){
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
        customer_bikou.value = shouhin[0].customer_bikou
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
    const resort = (index) =>{
      if(pic_list.value.length < sort){
        sort = 1
      }
      pic_list.value[index].sort = sort
      sort = Number(sort) + 1
    }



    const input_file_btn = (id) =>{
      document.getElementById(id).click()
    }
    const uploadfile = (id) =>{
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
          pic_list.value = [...response.data.filename]
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
      })
      .catch((error)=>{
        console_log(error)
        msg.value=`${shouhinNM.value} の登録に失敗しました`
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

    const order_count =(index,val) =>{
      let order = Number(shouhinMS.value[index].ordered)
      if(order + Number(val) < 0){
        shouhinMS.value[index].ordered = 0
      }else{
        shouhinMS.value[index].ordered = order + Number(val)
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
          zeiritu=row.ritu + 1
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


    onMounted(()=>{
      console_log(`onMounted : ${Where_to_use}`)
      if(Where_to_use==="shouhinMS"){
        get_shouhinMS()
        get_shouhinMS_newcd()
      }
      if(Where_to_use==="index"){
        get_shouhinMS_online()
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
      ins_shouhinMS,
      resort,
      shouhizei,
      zeikomi,
      order_count,
    }
  }
});