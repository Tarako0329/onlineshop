const review_post = (p_buylist,p_token) => createApp({
  setup() {
    const buylist = ref(p_buylist)
    const token = ref(p_token)
    const review_type = ref('shouhin') //or shop

    //axiosでajax_delins_review.phpを呼び出す関数
    const review_post_submit = (index) =>{
      console_log(index)
      console_log(buylist.value[index])
      const form = new FormData();
      form.append(`shop_id`, buylist.value[index].uid)
      form.append(`shouhinCD`, buylist.value[index].shouhinCD)
      form.append(`shouhinNM`, buylist.value[index].shouhinNM)
      form.append(`review`, buylist.value[index].review)
      form.append(`score`, buylist.value[index].score)
      form.append(`Contributor`, buylist.value[index].name)
      form.append(`NoName`, buylist.value[index].NoName)
      form.append(`orderNO`, buylist.value[index].orderNO)
      form.append(`csrf_token`, token.value)

      axios.post("ajax_delins_review.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response)=>{
        console_log(response.data)
        alert(response.data.MSG)
        token.value=response.data.token
        if(response.data.status==="alert-success"){
          buylist.value[index].btn_name = '更新'
        }else{
          //alert('レビュー投稿に失敗しました')
        }
      })
      .catch((error)=>{
        console_log(error)
        alert('レビュー投稿に失敗しました')
      })
      .finally(()=>{
      })
    }
    


    onMounted(()=>{
      console_log("onMounted")
      
    })
    onBeforeMount(()=>{
      console_log("onBeforeMount:")
    })


    return{
      buylist
      ,review_type
      ,review_post_submit
    }
  }
});
