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

const shouhinMS = () => createApp({
  setup() {
    const shouhinMS = ref([])
    
    const get_shouhinMS = () => {
      axios
      .get(`ajax_get_shouhinMS.php`)
      .then((response) => {
        console_log(response.data)
        shouhinMS.value = response.data.dataset
        console_log('get_shouhinMS succsess')
      })
      .catch((error)=>{
        console_log('get_shouhinMS.php ERROR')
        console_log(error)
        alert('リターンエラー：登録できませんでした')
      })
      .finally(()=>{
        //loader.value = false
      })
    }
    
    
    const tanka = ref(0)
    const zei = ref(0)
    const info = ref('')
    const get_shouhinMS_online = (shouhin) => {
      console_log(shouhin)
      let serch = document.getElementById(shouhin).value
      axios
      .get(`ajax_get_shouhinMS_online.php?f=${serch}`)
      .then((response) => {
        console_log(response.data)
        shouhinMS_online.value = response.data.dataset
        tanka.value = response.data.dataset[0].tanka==null?response.data.dataset[0].rez_tanka:response.data.dataset[0].tanka
        zei.value = response.data.dataset[0].zeikbn==null?response.data.dataset[0].rez_zeikbn:response.data.dataset[0].zeikbn
        info.value = response.data.dataset[0].infomation
        console_log('get_shouhinMS_online succsess')
      })
      .catch((error)=>{
        console_log('get_shouhinMS_online.php ERROR')
        console_log(error)
        alert('リターンエラー：登録できませんでした')
      })
      .finally(()=>{
        //loader.value = false
      })
    }

    const input_file_btn = (id) =>{
      document.getElementById(id).click()
    }
    const uploadfile = (id) =>{
      let i = 0
      const params = new FormData();

      while(document.getElementById(id).files[i]!==undefined){
        //console_log(document.getElementById(id).files[i])
        params.append(`user_file_name_${i}`, document.getElementById(id).files[i]);
        i = i+1
      }
      //console_log(params)

      axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        if(response.data.status==="success"){
          
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
    
    
    onMounted(()=>{
      get_shouhinMS()
    })


    return{
      shouhinMS,
      get_shouhinMS,
      tanka,
      zei,
      info,
      get_shouhinMS_online,
      input_file_btn,
      uploadfile,
    }
  }
});