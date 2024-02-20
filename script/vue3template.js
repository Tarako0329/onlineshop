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
    const test = ref('test')

    const get_shouhinMS = () => {//
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



    return{
      test,
    }
  }
});