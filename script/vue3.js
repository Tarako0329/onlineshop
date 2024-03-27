const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const admin_menu = (Where_to_use,p_token,user_hash) => createApp({//管理者メニュー
  setup() {
    const menu = ref([
      {name:'サイト設定',url : `configration.php?key=${user_hash}`},
      {name:'決済設定',url : `settlement.php?key=${user_hash}`},
      {name:'商品管理',url : `shouhinMS.php?key=${user_hash}`},
      {name:'受注管理',url : `order_management.php?key=${user_hash}`},
      {name:'発送サポート',url : `Unshipped_slip.php?key=${user_hash}`},
    ])

    onMounted(()=>{
      console_log(`onMounted admin_menu: ${Where_to_use}`)
      if(Where_to_use==="configration.php"){
        document.getElementById("menu_00").classList.add("active");
      }else if(Where_to_use==="settlement.php"){
        document.getElementById("menu_01").classList.add("active");
      }else if(Where_to_use==="shouhinMS.php"){
        document.getElementById("menu_02").classList.add("active");
      }else if(Where_to_use==="order_management.php"){
        document.getElementById("menu_03").classList.add("active");
      }else if(Where_to_use==="Unshipped_slip.php"){
        document.getElementById("menu_04").classList.add("active");
      }else{
      }
    })

    return{
      menu,
    }
  }
})


const Unsipped_slip = (Where_to_use,p_token,p_hash) => createApp({//発送サポート
  setup() {
    let token = p_token
    let hash = p_hash

    const Unsippedlist = ref([])
    const Unsippedlist_uchiwake = ref([])
    const FROM = ref('')
    const TO = ref(new Date().toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit",day: "2-digit"}).replaceAll('/', '-'))

    const get_unsipped_list = () => {
      axios
      .get(`ajax_get_unsipped.php?from=${FROM.value}&to=${TO.value}&hash=${hash}`)
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
    
    const pdf_url = computed(()=>{return `pdf_Unshipped_slip.php?from=${FROM.value}&to=${TO.value}`})

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
        console_log(response)
        shoplist.value = response.Users_online
      })
      document.getElementById("menu_Shops").classList.add("active");
    })

    return {
      shoplist,
    }
  }
})

const settlement = (Where_to_use,p_token,p_hash) => createApp({//サイト設定・支払情報
  setup() {
    let token = p_token
    let hash = p_hash

    const new_type = ref({types:"bank",payname:"",source:"",hosoku:"",flg:true})
    const pay_lists = ref([])
    const loader = ref(false)
    let stripe_mail = ''
    let stripe_id = 'nothing'
    let credit = ''

    const fileupload = (id,filesubname) => {
      UPLOADFILE(id,filesubname).then((response)=>{
        new_type.value.source=response.filename[0].filename
      })
    }

    const submit_payinfo = () =>{
      loader.value = true
      const form = new FormData()
      form.append('types',new_type.value.types)
      form.append('payname',new_type.value.payname)
      form.append('source',new_type.value.source)
      form.append('hosoku',new_type.value.hosoku)
      form.append('flg',new_type.value.flg)
      form.append('hash',hash)
      form.append('csrf_token',token)

      axios.post("ajax_ins_payinfo.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        token = response.data.csrf_create
        console_log(response.data)
        if(response.data.status==="success"){
          new_type.value.source = new_type.value.source.replace('temp/','')
          pay_lists.value.push(new_type.value)
          new_type.value = {types:"bank",payname:"",source:"",hosoku:"",flg:true}
        }else if(response.data.status==="warning"){
          alert('決済名が重複してます')
        }else{
          alert('登録処理エラー')
        }
      })
      .catch((error)=>{
        console_log(error)
        console_log(response)
        alert('登録処理エラー')
      })
      .finally(()=>{
        loader.value = false
      })
    }

    const upd_flg = (index) =>{
      loader.value = true
      const form = new FormData()
      form.append('payname',pay_lists.value[index].payname)
      form.append('flg',pay_lists.value[index].flg)

      form.append('hash',hash)
      form.append('csrf_token',token)

      axios.post("ajax_upd_userMSonline_pay.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        token = response.data.csrf_create
        console_log(response.data)
        if(response.data.status==="success"){
        }else if(response.data.status==="warning"){
          alert('決済名が重複してます')
        }else{
          alert('登録処理エラー')
        }
      })
      .catch((error)=>{
        console_log(error)
        console_log(response)
        alert('登録処理エラー')
      })
      .finally(()=>{
        loader.value = false
      })
    }

    const del_payinfo = (index) =>{
      loader.value = true
      const form = new FormData()
      form.append('payname',pay_lists.value[index].payname)
      form.append('source',pay_lists.value[index].source)
      form.append('types',pay_lists.value[index].types)

      form.append('hash',hash)
      form.append('csrf_token',token)

      axios.post("ajax_del_userMSonline_pay.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        token = response.data.csrf_create
        console_log(response.data)
        if(response.data.status==="success"){
          pay_lists.value.splice(index,1)
          alert('決済情報を削除しました')
        }else if(response.data.status==="warning"){
          //alert('決済名が重複してます')
        }else{
          alert('削除処理エラー')
        }
      })
      .catch((error)=>{
        console_log(error)
        console_log(response)
        alert('削除処理エラー')
      })
      .finally(()=>{
        loader.value = false
      })
    }

    const create_stripe = () =>{
      axios.get(`ajax_create_stripe.php?mail=${stripe_mail}&hash=${hash}&id=${stripe_id}`)
      .then((response)=>{
        console_log(response.data)
      })
      .catch((error)=>{
        console_log(error)
      })
      .finally(()=>{})
    }

    onMounted(()=>{
      GET_USER2()
      .then((response)=>{
        console_log(response.Users_online[0].mail)
        pay_lists.value = response.Users_online_payinfo
        stripe_mail = response.Users_online[0].mail
        stripe_id = response.Users_online[0].stripe_id
        credit = response.Users_online[0].credit
      })
    })

    onBeforeMount(()=>{
      console_log("onBeforeMount:")
      if(pay_lists.value.length===0){
        //pay_lists.value.push(new_type.value)
      }
    })

    return {
      new_type,
      pay_lists,
      loader,
      fileupload,
      submit_payinfo,
      upd_flg,
      del_payinfo,
      create_stripe,
    }
  }
})


