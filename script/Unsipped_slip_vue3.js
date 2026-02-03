//const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;

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



