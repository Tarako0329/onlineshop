const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;

const admin_menu = (Where_to_use,p_token,user_hash) => createApp({//管理者メニュー
  setup() {
    const menu = ref([
      {name:'サイト設定',url : `configration.php?key=${user_hash}`},
      {name:'決済設定',url : `settlement.php?key=${user_hash}`},
      {name:'商品管理',url : `shouhinMS.php?key=${user_hash}`},
      {name:'受注管理',url : `order_management.php?key=${user_hash}`},
      {name:'発送サポート',url : `Unshipped_slip.php?key=${user_hash}`},
      {name:'ご利用明細',url : `seikyu_yotei.php?key=${user_hash}`},
      {name:'利用規約',url : `kiyaku.php?key=${user_hash}`},
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
      }else if(Where_to_use==="seikyu_yotei.php"){
        document.getElementById("menu_05").classList.add("active");
      }else if(Where_to_use==="kiyaku.php"){
        document.getElementById("menu_06").classList.add("active");
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



