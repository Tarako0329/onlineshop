//js 共通関数格納予定
const console_log=(log,lv)=>{
  //lv:all=全環境 undefined=本番以外
  //console.log(lv)
  if(lv==="all"){
    console.log(log)
  }/*else if(lv==="lv2" && KANKYO!=="Product"){
    console.log(log)
  }*/else if((lv==="lv3" || lv===undefined) && (KANKYO!=="Product")){
    //console.log(KANKYO)
    console.log(log)
  }else{
    return 0;
  }
}

const COPY_TARGET = (id) =>{
  let copyTarget = document.getElementById(id).innerText;;
  // 選択しているテキストをクリップボードにコピーする
  navigator.clipboard.writeText(copyTarget);

}

//グローバル関数
const GET_USER2 = ()=>{//サイト設定情報取得
	return new Promise((resolve, reject) => {
		GET_USER_SHORI(resolve);
	});
}
const GET_USER_SHORI = (resolve) =>{
  let obj
  axios
  .get(`ajax_get_usersMSonline.php`)
  .then((response) => {
    obj = response.data
    console_log('ajax_get_usersMSonline succsess')
  })
  .catch((error)=>{
    console_log('ajax_get_usersMSonline.php ERROR')
    console_log(error)
  })
  .finally(()=>{
    resolve(obj)
  })
}

const UPLOADFILE = (id) =>{//写真アップロード処理・写真をアップしファイルパスを取得
  const params = new FormData();
  
  let i = 0
  while(document.getElementById(id).files[i]!==undefined){
    params.append(`user_file_name_${i}`, document.getElementById(id).files[i]);
    i = i+1
  }
  params.append('shouhinCD',shouhinCD.value)
  loader.value = true
  axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
  .then((response)=>{
    console_log(response.data)
    if(response.data.status==="success"){
      pic_list.value = [...pic_list.value,...response.data.filename]
    }else{
      alert('写真アップロードエラー')
    }
  })
  .catch((error)=>{
    console_log(error)
    alert('写真アップロードERROR')
  })
  .finally(()=>{
    loader.value = false
  })
}

const LINE_PUSH = (ID,MSG) =>{
  const form = new FormData();
  form.append(`LINE_USER_ID`, ID)
  form.append(`MSG`, MSG)

  axios.post("line_push_msg.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
  .then((response) => {
    console_log('line_push_msg succsess')
  })
  .catch((error)=>{
    console_log('line_push_msg.php ERROR')
    console_log(error)
  })
  .finally(()=>{
  })
}
