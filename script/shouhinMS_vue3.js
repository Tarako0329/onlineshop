//const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const shouhinMS = (Where_to_use,p_token,p_hash) => createApp({//商品マスタ管理
	setup() {
		const zeiMS = [
			{
				zeikbn:"0",
				ritu:0
			},
			{
				zeikbn:"1001",
				ritu:0.08
			},
			{
				zeikbn:"1101",
				ritu:0.1
			},
		]
		let token = p_token
		let hash = p_hash
		const msg=ref('')
		const loader = ref(false)

		const shouhinMS = ref([])
		const shouhinMS_pic = ref([])
		
		const mode = ref('new')
		const get_shouhinMS = (serch) => {
			let url=`ajax_get_shouhinMS.php?f=${serch}`
			console_log('get_shouhinMS start')
			
			axios.get(url)
			.then((response) => {
				console_log(response.data)
				shouhinMS.value = [...response.data.dataset]
				console_log('get_shouhinMS succsess')
			})
			.catch((error)=>{
				console_log(error)
				//alert('リターンエラー：商品マスタ取得失敗')
			})
			.finally(()=>{
				//loader.value = false
			})
		}
		const get_shouhinMS_newcd = () => {
			let url=`ajax_get_shouhinMS_newcd.php`
			console_log('get_shouhinMS_newcd start')
			
			axios.get(url)
			.then((response) => {
				console_log(response.data)
				shouhinCD.value = response.data
				console_log('get_shouhinMS_newcd succsess')
			})
			.catch((error)=>{
				console_log(error)
				alert('リターンエラー：商品マスタnewCD取得失敗')
			})
			.finally(()=>{
				//loader.value = false
			})
		}
		
		const shouhinCD = ref('')
		const shouhinNM = ref('')
		const status = ref('show')
		const tanka = ref(0)
		const zei = ref(1101)
		const midasi = ref('')
		const info = ref('')
		const customer_bikou = ref('ご要望等ございましたらご記入ください。')
		const pic_list = ref([])
		const rez_shouhinCD = ref('')
		const rez_shouhinNM = ref('')
		const get_shouhinMS_online = (serch) => {
			axios
			.get(`ajax_get_shouhinMS_online.php?f=${serch}`)
			.then((response) => {
				if(response.data.alert==="success"){
					shouhinMS.value = [...response.data.dataset]
					shouhinMS_pic.value = [...response.data.pic_set]
					console_log('get_shouhinMS_online succsess')
					//console_log(response.data.pic_set)
				}else{
					console_log('get_shouhinMS_online succsess:NoData')
				}
			})
			.catch((error)=>{
				console_log('get_shouhinMS_online.php ERROR')
				console_log(error)
			})
			.finally(()=>{
				//loader.value = false
			})
		}

		watch(mode,()=>{//マスタ登録モードに合わせて商品名のリストを取得する
			if(mode.value==="new"){
				clear_ms()
				shouhinMS.value = []
				get_shouhinMS()
				get_shouhinMS_newcd()
			}else if(mode.value==="upd"){
				clear_ms()
				shouhinMS.value = []
				get_shouhinMS_online()
			}else{
				return
			}
		})

		watch(shouhinNM,()=>{//入力された商品名からマスタ情報を取得
			let shouhin = shouhinMS.value.filter((row)=>{
				return row.shouhinNM === shouhinNM.value
			})
			console_log(shouhin)
			if(shouhin.length!==0){
				tanka.value = shouhin[0].tanka
				status.value = shouhin[0].status
				zei.value = String(shouhin[0].zeikbn)
				info.value = shouhin[0].infomation
				customer_bikou.value = mode.value==="new"?customer_bikou.value:shouhin[0].customer_bikou
				midasi.value = shouhin[0].short_info
				pic_list.value=[]
				shouhinMS_pic.value.forEach((row)=>{
					if(row.shouhinCD===shouhin[0].shouhinCD){
						pic_list.value.push(row)
					}
				})
				console_log(pic_list.value)
				if(mode.value==="upd"){
					shouhinCD.value = shouhin[0].shouhinCD
				}
			}

			if(mode.value==="new"){
				get_shouhinMS_newcd()
			}else{
			}
		})

		let sort = 1
		const resort = (index) =>{//画像の並び順設定
			if(pic_list.value.length < sort){
				sort = 1
			}
			pic_list.value[index].sort = sort
			sort = Number(sort) + 1
		}

		const input_file_btn = (id) =>{//アップロードボタン
			document.getElementById(id).click()
		}
		const uploadfile = (id) =>{//写真アップロード処理・写真をアップしファイルパスを取得
			const params = new FormData();
			
			let i = 0
			while(document.getElementById(id).files[i]!==undefined){
				params.append(`user_file_name_${i}`, document.getElementById(id).files[i]);
				i = i+1
			}
			params.append('fileparam',shouhinCD.value)
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

		const pic_delete = (filepass) =>{
			//アップされたファイルを削除
			//マスタに登録されたレコードを削除
			//pic_list[]からレコード削除
			const form = new FormData();
			form.append(`pic`, filepass)
			form.append(`csrf_token`, token)
			form.append(`hash`, hash)

			axios.post("ajax_file_delete.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
			.then((response)=>{
				console_log(response.data)
				if(response.data.status==="alert-success"){
					//画面のクリア
					pic_list.value.forEach((row,index)=>{
						if(row.filename===filepass){pic_list.value.splice(index,1)}
					})
					msg.value=`${filepass} を削除しました`

				}else{
					msg.value=`${filepass} の削除に失敗しました`
				}
				token = response.data.csrf_create
			})
			.catch((error,response)=>{
				console_log(error)
				msg.value=`${filepass} の削除に失敗しました`
				token = response.data.csrf_create
			})
			.finally(()=>{
				//loader.value = false
			})

		}

		const ins_shouhinMS = ()=>{
			let msg = ''
			if(shouhinNM.value == ''){
				msg = msg + ' 商品名'
			}
			if(tanka.value == ''){
				msg = msg + ' 単価'
			}
			if(midasi.value == ''|| midasi.value == null){
				msg = msg + ' 商品説明（見出し）'
			}
			if(info.value == '' || info.value == null){
				msg = msg + ' 商品説明（詳細）'
			}
			if(pic_list.value.length === 0){
				msg = msg + ' 写真'
			}
			if(msg.length != 0){
				alert(`${msg} を設定してください。`)
				return
			}
			loader.value = true
			const form = new FormData();
			form.append(`shouhinCD`, shouhinCD.value)
			form.append(`shouhinNM`, shouhinNM.value)
			form.append(`status`, status.value)
			form.append(`tanka`, tanka.value)
			form.append(`zeikbn`, zei.value)
			form.append(`shouhizei`, shouhizei.value)
			form.append(`infomation`, info.value)
			form.append(`customer_bikou`, customer_bikou.value)
			form.append(`short_info`, midasi.value)
			form.append(`csrf_token`, token)
			form.append(`hash`, hash)
			let i = 0
			pic_list.value.forEach((row)=>{
				form.append(`user_file_name[${i}][sort]`,row.sort)
				form.append(`user_file_name[${i}][filename]`,row.filename)
				i=i+1
			})
			axios.post("ajax_delins_shouhinMS.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
			.then((response)=>{
				console_log(response.data)
				if(response.data.status==="alert-success"){
					//画面のクリア
					if(mode.value==="new"){
						get_shouhinMS()
						get_shouhinMS_newcd()
					}else if(mode.value==="upd"){
						get_shouhinMS_online()
					}
					//msg.value=`${shouhinNM.value} を登録しました`
					alert(`${shouhinNM.value} を登録しました`)
					clear_ms()
				}else{
					//msg.value=`${shouhinNM.value} の登録に失敗しました`
					alert(`${shouhinNM.value} の登録に失敗しました`)
				}
				token = response.data.csrf_create
			})
			.catch((error,response)=>{
				console_log(error)
				//msg.value=`${shouhinNM.value} の登録に失敗しました`
				alert(`${shouhinNM.value} の登録に失敗しました`)
				token = response.data.csrf_create
			})
			.finally(()=>{
				loader.value = false
			})
		}
		
		const clear_ms = () =>{
			console_log('clear_ms')
			shouhinNM.value = ''
			status.value = 'show'
			tanka.value = 0
			zei.value = 1101
			midasi.value = ''
			info.value = ''
			customer_bikou.value='ご要望等ございましたらご記入ください。'
			pic_list.value=[]
		}

		const shouhizei = computed(()=>{//消費税計算
			let zeiritu = 0.1
			zeiMS.forEach((row)=>{
				if(row.zeikbn===zei.value){
					zeiritu=row.ritu
				}
			})
			console_log(zeiritu)
			let num1 = new Decimal(tanka.value);
			let num2 = new Decimal(zeiritu);
			//console_log(num1.mul(num2).toNumber());
			return num1.mul(num2).toNumber()
		})

		const zeikomi = computed(()=>{//税込価格計算
			let zeiritu = 0.1 + 1
			zeiMS.forEach((row)=>{
				if(row.zeikbn===zei.value){
					zeiritu=Number(row.ritu) + Number(1)
				}
			})
			console_log(zeiritu)
			let num1 = new Decimal(tanka.value);
			let num2 = new Decimal(zeiritu);
			//console_log(num1.mul(num2).toNumber());
			return num1.mul(num2).toNumber()
		})
		
		watch(msg,()=>{
			console_log('watch msg => '+msg.value)
			setTimeout(()=>{msg.value=""}, 3000);//3s
			
		})

		onMounted(()=>{
			console_log(`onMounted : ${Where_to_use}`)
			if(Where_to_use==="shouhinMS.php"){
				get_shouhinMS()
				get_shouhinMS_newcd()
			}
		})

		return{
			msg,
			loader,
			mode,
			shouhinMS,
			shouhinMS_pic,
			get_shouhinMS,
			shouhinCD,
			shouhinNM,
			status,
			tanka,
			zei,
			midasi,
			info,
			customer_bikou,
			pic_list,
			rez_shouhinCD,
			rez_shouhinNM,
			get_shouhinMS_online,
			input_file_btn,
			uploadfile,
			pic_delete,
			ins_shouhinMS,
			resort,
			shouhizei,
			zeikomi,
		}
	}
});
