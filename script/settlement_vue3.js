const settlement = (Where_to_use,p_token,p_hash) => createApp({//サイト設定・支払情報
  setup() {
    let token = p_token
    let hash = p_hash

    const new_type = ref({types:"bank",payname:"",source:"",hosoku:"",flg:true})
    const pay_lists = ref([])
    const loader = ref(false)
    const stripe_id = ref('nothing')
    const btn_name = ref('')
    const stripe_dashboard = ref(false)
    const stripe_dashboard_link = ref('https://dashboard.stripe.com/account/status')
    const stripe_url = ref('https://stripe.com/jp')
    const credit = ref('')
    const Stripe_Approval_Status = ref('')

    
    let stripe_mail = ''

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
          alert('登録処理失敗')
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

    const upd_credit = () =>{
      loader.value = true
      const form = new FormData()
      form.append('credit',credit.value)

      form.append('hash',hash)
      form.append('csrf_token',token)

      axios.post("ajax_upd_userMSonline_credit.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
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
      loader.value=true
      axios.get(`ajax_create_stripe.php?mail=${stripe_mail}&hash=${hash}&id=${stripe_id.value}`)
      .then((response)=>{
        console_log(response.data)
        if(response.data.status='success'){
          window.location.href = response.data.link;	
        }else{
          alert('処理に失敗しました')
        }
      })
      .catch((error)=>{
        console_log(error)
      })
      .finally(()=>{
        loader.value=false
      })
    }

    onMounted(()=>{
      GET_USER2()
      .then((response)=>{
        console_log(response.Users_online[0].mail)
        pay_lists.value = response.Users_online_payinfo
        stripe_mail = response.Users_online[0].mail
        stripe_id.value = response.Users_online[0].stripe_id
        credit.value = response.Users_online[0].credit
        Stripe_Approval_Status.value = response.Users_online[0].Stripe_Approval_Status
        if(stripe_id.value==="none"){
          btn_name.value="Stripeアカウントの登録を始める"
        //}else if(credit.value==="unable"){
        }else if(Stripe_Approval_Status.value==="Registering"){  //登録中
          btn_name.value="Stripeアカウントの登録を再開する"
        }
        //if(credit.value!=="unable"){
        if(Stripe_Approval_Status.value==="Registered"){  //登録済み（Stripeの承認はまだ）
          stripe_dashboard.value=true
        }
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
      btn_name,
      stripe_id,
      stripe_dashboard,
      stripe_url,
      stripe_dashboard_link,
      credit,
      Stripe_Approval_Status,
      fileupload,
      submit_payinfo,
      upd_flg,
      del_payinfo,
      create_stripe,
      upd_credit,
    }
  }
})
