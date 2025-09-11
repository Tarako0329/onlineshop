//js 共通関数格納予定
const console_log=(log,lv)=>{
  //lv:all=全環境 undefined=本番以外
  //console.log(`lv:${lv}`)
  
  if(lv=="all"){
    console.log(log)
  }else if((lv==="lv3" || lv===undefined) && (KANKYO!=="Product")){
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
const COPY_TARGET2 = (id) =>{
  console_log(document.getElementById(id))
  let copyTarget = document.getElementById(id).value;
  // 選択しているテキストをクリップボードにコピーする
  navigator.clipboard.writeText(copyTarget);

}

function GET_KONGETU() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0'); // 月は0から始まるため+1し、2桁に揃える
  return `${year}-${month}`;
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
    //console_log(obj)
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

const UPLOADFILE = (id,filesubname)=>{//サイト設定情報取得
	return new Promise((resolve, reject) => {
		UPLOADFILE_SHORI(id,filesubname,resolve);
	});
}
const UPLOADFILE_SHORI = (id,filesubname,resolve) =>{//写真アップロード処理・写真をアップしファイルパスを取得
  //id:[input type=file]に設定してるID
  //アップロードするファイルのサブ名称　xxxx+filesubname+xxx.png みたいになる
  let obj
  const params = new FormData();
  
  let i = 0
  while(document.getElementById(id).files[i]!==undefined){
    params.append(`user_file_name_${i}`, document.getElementById(id).files[i]);
    i = i+1
  }
  params.append('filesubname',filesubname)
  axios.post("ajax_loader.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
  .then((response)=>{
    console_log(response.data)
    obj = response.data
    if(response.data.status==="success"){
      
    }else{
      alert('写真アップロードエラー')
    }
  })
  .catch((error)=>{
    obj = error
    console_log(error)
    alert('写真アップロードERROR')
  })
  .finally(()=>{
    resolve(obj)
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

/*const GET_AI_POST = (p_hinmei,p_sort_info,p_information,p_hinCD,p_hash,p_yagou,p_sns_type) =>{
  return new Promise((resolve, reject) => {
    let obj
    const params = new FormData();
    params.append(`hinmei`, p_hinmei);
    params.append(`sort_info`, p_sort_info);
    params.append(`information`, p_information);
    params.append(`hinCD`,p_hinCD);
    params.append(`hash`,p_hash);
    params.append(`yagou`,p_yagou);
    params.append(`sns_type`,p_sns_type);

    axios.post("ajax_Gemini_Answer_POST.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
    .then((response) => {
      //console_log(response)
      console_log(response.data)
      obj = response.data
      //console_log(obj)
      console_log('ajax_Gemini_Answer succsess')
      resolve(obj)
    })
    .catch((error)=>{
      console_log('ajax_Gemini_Answer.php ERROR')
      console_log(error)
      reject(error)
    })
    .finally(()=>{
      
    })
  })
}*/

/*const GET_AI_SEO = (p_hinmei,p_sort_info,p_information) =>{
  return new Promise((resolve, reject) => {
    let obj
    const params = new FormData();
    params.append(`hinmei`, p_hinmei);
    params.append(`sort_info`, p_sort_info);
    params.append(`information`, p_information);

    axios.post("ajax_Gemini_Answer_SEO.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
    .then((response) => {
      //console_log(response)
      console_log(response.data)
      obj = response.data
      //console_log(obj)
      console_log('ajax_Gemini_Answer succsess')
      resolve(obj)
    })
    .catch((error)=>{
      console_log('ajax_Gemini_Answer.php ERROR')
      console_log(error)
      reject(error)
    })
    .finally(()=>{
      
    })
  })
}*/

const SET_ELEM_HEIGHT = (p_id,p_px) =>{
  console_log(`start SET_ELEM_HEIGHT`)
  document.getElementById(p_id).style.height=`${p_px}`
}