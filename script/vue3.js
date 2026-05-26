const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;

const shops = (Where_to_use,p_token) => createApp({//サイト設定
  setup() {
    const shoplist = ref([])

    onMounted(()=>{
      axios.get("ajax_get_ShopList.php")
      .then((response)=>{
        console_log(response.data)
        shoplist.value = response.data.Users_online
      })
      document.getElementById("menu_Shops").classList.add("active");
    })

    return {
      shoplist,
    }
  }
})


