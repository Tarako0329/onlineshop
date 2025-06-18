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
		const loader2 = ref(false)
		const RTURL = ref(HTTP)

		const shouhinMS = ref([])
		const shouhinMS_pic = ref([])
		
		const mode = ref('upd')
		const disp = ref('none')
		//const shouhin_table = ref({maxHeight:'100px',backgroundColor:'blanchedalmond'})
		const shouhin_table = computed(()=>{
			if(disp.value!=="none"){
				return {maxHeight:'100px',backgroundColor:'blanchedalmond'}
			}else{
				return {maxHeight:'400px',backgroundColor:'blanchedalmond'}
			}
		})
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
		
		const uid = ref('')
		const x_id = ref('')
		const shouhinCD = ref('')
		const shouhinNM = ref('')
		const status = ref('show')
		const limited_cd = ref('')
		const tanka = ref(0)
		const zei = ref(1101)
		const midasi = ref('')
		const info = ref('')
		const haisou = ref('')
		const hash_tag = ref('')
		const yagou = ref('')
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

		const cg_mode =(p_mode) =>{
			if(shouhinNM.value && mode.value !== p_mode){
				if(confirm('現在の変更内容は破棄されますがよろしいですか？')==false){
					console_log(`破棄しない`)
					mode.value=(p_mode==="new")?"upd":"new"
					return 0
				}
			}
			mode.value=p_mode
		}
		watch(mode,()=>{//マスタ登録モードに合わせて商品名のリストを取得する
			if(mode.value==="new"){
				clear_ms()
				shouhinMS.value = []
				//get_shouhinMS()
				get_shouhinMS_newcd()
				disp.value = 'show'
			}else if(mode.value==="upd"){
				clear_ms()
				shouhinMS.value = []
				get_shouhinMS_online()
				disp.value = 'none'
			}else{
				return
			}
		})

		const set_shouhinNM = (p_shouhinNM) =>{
			if(shouhinNM.value){
				if(confirm('現在の変更内容は破棄されますがよろしいですか？')==false){
					return 0
				}
			}
			if(!p_shouhinNM){
				clear_ms()
				disp.value = 'none'
				return 0
			}else{
				shouhinNM.value = p_shouhinNM
				disp.value = 'show'
			}
			//mode.value = 'upd'
			
		}

		watch(shouhinNM,()=>{//入力された商品名からマスタ情報を取得
			let shouhin = shouhinMS.value.filter((row)=>{
				return row.shouhinNM === shouhinNM.value
			})
			console_log(shouhin)
			if(shouhin.length!==0){
				uid.value = shouhin[0].uid
				x_id.value = shouhin[0].x_id
				tanka.value = shouhin[0].tanka
				status.value = shouhin[0].status
				zei.value = String(shouhin[0].zeikbn)
				info.value = shouhin[0].infomation
				haisou.value = shouhin[0].haisou
				hash_tag.value = shouhin[0].hash_tag
				yagou.value = shouhin[0].yagou
				customer_bikou.value = mode.value==="new"?customer_bikou.value:shouhin[0].customer_bikou
				midasi.value = shouhin[0].short_info
				limited_cd.value = shouhin[0].limited_cd
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
			}else{
				//clear_ms()
			}

			if(mode.value==="new"){
				get_shouhinMS_newcd()
			}else{
			}
			post_sns.value = {
				'URL':`${HTTP}product.php?id=${uid.value}-${shouhinCD.value}&z=`
				,'URL_line':encodeURIComponent(`${HTTP}product.php?id=${uid.value}-${shouhinCD.value}&z=ln`)
				,'text':''}
		})

		let sort = 1
		const resort = (index) =>{//画像の並び順設定
			if(pic_list.value.length < sort){
				sort = 1
			}
			pic_list.value[index].sort = sort
			sort = Number(sort) + 1
		}

		const pic_sort_chk = computed(()=>{
			//pic_list.value[].sortの値が重複していたらメッセージを返す
			let msg = ''
			let sort_list = []
			pic_list.value.forEach((row)=>{
				if(sort_list.indexOf(row.sort)!==-1){
					msg = '表示順が重複してます。「表示順」ボタンで写真のスライド順を調整してください。'
				}else{
					sort_list.push(row.sort)
				}
			})
			return msg
			
		})

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
			if(confirm("削除は即反映されます。本当に削除しますか？")===false){
				return 0
			}
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
				msg = msg + ' 商品名、'
			}
			if(status.value=='limited' && !limited_cd.value){
				msg = msg + ' 限定販売特別コード、'
			}
			if(tanka.value == ''){
				msg = msg + ' 単価、'
			}
			if(midasi.value == ''|| midasi.value == null){
				msg = msg + ' 商品説明（見出し）、'
			}
			if(info.value == '' || info.value == null){
				msg = msg + ' 商品説明（詳細）、'
			}
			if(haisou.value == '' || haisou.value == null){
				msg = msg + ' 送料・配送・納期、'
			}
			if(pic_list.value.length === 0){
				msg = msg + ' 写真'
			}
			if(msg.length != 0){
				alert(`${msg} を設定してください。`)
				return
			}
			loader.value = true
			//let p_hash_tag = hash_tag.value.replace('、',',')
			hash_tag.value = hash_tag.value.replace('、',',')
			hash_tag.value = hash_tag.value.replace('＃','#')
			hash_tag.value = hash_tag.value.replace(' ','')
			hash_tag.value = hash_tag.value.replace('　','')
			hash_tag.value = hash_tag.value.replace('?','')
			hash_tag.value = hash_tag.value.replace('？','')
			hash_tag.value = hash_tag.value.replace('&','')
			hash_tag.value = hash_tag.value.replace('＆','')
			const form = new FormData();
			form.append(`shouhinCD`, shouhinCD.value)
			form.append(`shouhinNM`, shouhinNM.value)
			form.append(`status`, status.value)
			form.append(`limited_cd`, limited_cd.value)
			form.append(`tanka`, tanka.value)
			form.append(`zeikbn`, zei.value)
			form.append(`shouhizei`, shouhizei.value)
			form.append(`infomation`, info.value)
			form.append(`haisou`, haisou.value)
			form.append(`hash_tag`, hash_tag.value)
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
						//get_shouhinMS()
						get_shouhinMS_newcd()
					}else if(mode.value==="upd"){
						get_shouhinMS_online()
					}
					alert(`${shouhinNM.value} を登録しました`)
					clear_ms()
					disp.value='none'
				}else{
					alert(`${shouhinNM.value} の登録に失敗しました`)
				}
				token = response.data.csrf_create
			})
			.catch((error,response)=>{
				console_log(error)
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
			hash_tag.value=''
			customer_bikou.value='ご要望等ございましたらご記入ください。'
			pic_list.value=[]
			AI_answer.value = {'posts':[{'tags':'def'}]}
			AI_answer_seo.value = {'introductions':['def']}
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

		const upd_status = (p_status,p_shouhinCD) =>{
			console_log(p_status)
			if(p_status==='del'){
				if(confirm('本当に削除しますか？')===false){
					alert('処理を中止しました')
					return 0
				}
			}
			const form = new FormData();
			form.append(`shouhinCD`, p_shouhinCD)
			form.append(`status`, p_status)
			form.append(`csrf_token`, token)
			form.append(`hash`, hash)
			axios.post("ajax_upd_shouhinMS_status.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
			.then((response)=>{
				console_log(response.data)
				if(response.data.status==="alert-success"){
					//画面のクリア
					msg.value=response.data.MSG
					get_shouhinMS_online()
				}else{
					msg.value=`販売ステータス変更に失敗しました。${response.data.MSG}`
				}
				token = response.data.csrf_create
				if(p_status==='del'){
					alert('削除しました')
					clear_ms()
					disp.value = 'none'
				}
			})
			.catch((error,response)=>{
				console_log(error)
				msg.value=`販売ステータス変更に失敗しました。${error}`
				token = response.data.csrf_create
			})
			.finally(()=>{
				//loader.value = false
			})

		}

		const open_product_page = (id,p_shouhinNM) =>{
			if(confirm(`${p_shouhinNM} の販売ページを確認しますか？（新しいタブで販売ページを開きます）`)===false){
				return 0
			}
			const url = document.getElementById(id).innerText
			window.open(url)
		}
		const copy_target = (id,p_shouhinNM) =>{
      COPY_TARGET(id)
			msg.value = `${p_shouhinNM} 販売ページのURLをコピーしました。`
    }
		const copy_sns = (id) =>{
      //COPY_TARGET2(id)
			navigator.clipboard.writeText(post_sns.value.text + post_sns.value.URL +"fb "+ post_sns.value.tag_disp)
			msg.value = `紹介文をコピーしました。`
    }

		const AI_answer = ref({'posts':[{'tags':'def'}]})
		const sns_char_cnt = computed(()=>{
			//sns_typeに応じて使用できる文字数を返す
			let cnt = 0
			if(sns_type.value==='X(twitter)'){
				cnt = 90
			}else if(sns_type.value==='公式Line'){
				cnt = 500
			}else if(sns_type.value==='FACEBOOK'){
				cnt = 500
			}else if(sns_type.value==='instagram'){
				cnt = 500
			}else{
				cnt = 140
			}
			return cnt
			
		})
		const get_AI_post = () =>{
			loader2.value = true
			AI_answer.value = {'posts':[{'tags':'def','texts':'def'}]}
			document.getElementById('gemini_btn').disabled = true
			if(Where_to_use==='shouhinMS.php'){document.getElementById('gemini_seo_btn').disabled = true}
			console_log('get_AI_post start')

			const params = new FormData();
			/*const Article = `
			凄腕インフルエンサーとして${sns_type.value}でバズるハッシュタグを10個と,購買意欲を掻き立てる日本語の投稿例を３つJSON形式{"posts":{"tags":[tag1,tag2], "texts":[{text:"",tags:[...],URL:""}]}}で出力。
			投稿例は日本語で${sns_char_cnt.value}文字程度でハッシュタグ不要。投稿例はtexts.textに格納。URLはtexts.URLに格納。ハッシュタグはtexts.tagsに格納。
			${timing.value}『商品名：[${shouhinNM.value}],アピールポイント：[${midasi.value}], 商品の詳細・仕様・成分など：[${info.value}]』
			`*/
			const Article = `
			凄腕インフルエンサーとして${sns_type.value}でバズるハッシュタグを10個と,購買意欲を掻き立てる日本語の投稿例を３つ作成してください。
			下記のJSONスキーマに厳密に従ってJSONを出力してください。
			投稿例は日本語で${sns_char_cnt.value}文字程度でハッシュタグ不要。
			${timing.value}『商品名：[${shouhinNM.value}],アピールポイント：[${midasi.value}], 商品の詳細・仕様・成分など：[${info.value}], 商品url:${post_sns.value.URL}』
			`
			const response_schema = {
        'type': 'object',
        'properties': {
            'posts': {
                'type': 'object',
                'properties': {
                    'tags': { // For the 10 general hashtags
                        'type': 'array',
                        'items': {
                            'type': 'string',
                            'description': 'バズるハッシュタグ (例: "便利グッズ")'
                        },
                        'description': 'SNSでバズるためのハッシュタグのリスト (10個)'
                    },
                    'texts': { // For the 3 post examples
                        'type': 'array',
                        'items': {
                            'type': 'object',
                            'properties': {
                                'text': {
                                    'type': 'string',
                                    'description': 'SNS投稿文例 (このテキスト内にハッシュタグは含めないでください)'
                                },
                                'tags': { // Hashtags for this specific post example
                                    'type': 'array',
                                    'items': {
                                        'type': 'string',
                                        'description': 'この投稿例に関連するハッシュタグ (例: "新商品紹介")'
                                    },
                                    'description': 'この投稿例に推奨されるハッシュタグのリスト'
                                },
                                'URL': {
                                    'type': 'string',
                                    'description': '商品ページの完全なURL'
                                }
                            },
                            'required': ['text', 'tags', 'URL'] // Each post example must have these
                        },
                        'description': '購買意欲を掻き立てる日本語のSNS投稿例 (3つ)'
                    }
                },
                'required': ['tags', 'texts']
            }
        },
        'required': ['posts']
    	}

			params.append(`Article`, Article);
			params.append(`type`, 'one');
			params.append(`answer_type`, 'json')
			params.append(`response_schema`, JSON.stringify(response_schema))

			//GET_AI_POST(shouhinNM.value,`${timing.value}${midasi.value}`,info.value,shouhinCD.value,hash,yagou.value,sns_type.value)
			axios.post("ajax_chk_gemini.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
			.then((response) => {
				console_log('get_AI_post succsess')
				console_log(response.data)
				AI_answer.value = response.data.result
				document.getElementById('modalon').click()
			})
			.catch((error)=>{
				console_log('get_AI_post.php ERROR')
				console_log(error)
				alert('Google AI が不調なようです。時間を空けて再実行してみてください。')
			})
			.finally(()=>{
				loader2.value = false
				document.getElementById('gemini_btn').disabled=false
				if(Where_to_use==='shouhinMS.php')document.getElementById('gemini_seo_btn').disabled=false
			})
		}
		const tags_add = (p_tag) =>{
			if(hash_tag.value.indexOf(p_tag)===-1){
				console_log('ない。たす')
				hash_tag.value = hash_tag.value + String(p_tag) + ','
			}else{
				console_log('ある。けす')
				hash_tag.value = hash_tag.value.replace(p_tag+',','')
			}
			console_log(hash_tag.value)
		}

		const AI_answer_seo = ref({'introductions':['def']})
		const get_AI_seo = () =>{
			loader2.value = true
			AI_answer_seo.value = {'introductions':['def']}
			document.getElementById('gemini_btn').disabled = true
			document.getElementById('gemini_seo_btn').disabled = true
			console_log('get_AI_post start')

			const params = new FormData();
			/*const Article = `商品販売SEO対策のプロとして、GOOGLE検索でクリックしたくなる魅力的な紹介文(日本語100文字程度)を5つ、
			javascriptでそのまま使えるJSON形式{introductions:[{rei:紹介文},{rei:紹介文},{rei:紹介文}]}で提案してください。JSON以外は不要です。
			下記のJSONスキーマに厳密に従ってJSONを出力してください。
			商品名：[${shouhinNM.value}],アピールポイント：[${midasi.value}], 商品の詳細・仕様・成分など：[${info.value}]`*/
			const Article = `商品販売SEO対策のプロとして、GOOGLE検索でクリックしたくなる魅力的な紹介文(日本語100文字程度)を5つ、
			下記のJSONスキーマに厳密に従ってJSONを出力してください。
			商品名：[${shouhinNM.value}],アピールポイント：[${midasi.value}], 商品の詳細・仕様・成分など：[${info.value}]`
			const response_schema = {
        'type': 'object',
        'properties': {
            'introductions': {
                'type': 'array',
                'items': {
                    'type': 'object',
                    'properties': {
                        'rei': {
                            'type': 'string',
                            'description': '魅力的な紹介文 (日本語100文字程度)'
                        }
                    },
                    'required': ['rei']
                },
                'description': 'GOOGLE検索でクリックしたくなる魅力的な紹介文のリスト (5つ)'
            }
        },
        'required': ['introductions']
    	}
    	
			params.append(`Article`, Article);
			params.append(`type`, 'one');
			params.append(`answer_type`, 'json')
			params.append(`response_schema`, response_schema)
	
			//GET_AI_SEO(shouhinNM.value,midasi.value,info.value)
			axios.post("ajax_chk_gemini.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
			.then((response) => {
				console_log('get_AI_seo succsess')
				console_log(response)
				AI_answer_seo.value = response.data.result
				document.getElementById('modalon_seo').click()
			})
			.catch((error)=>{
				console_log('get_AI_seo.php ERROR')
				console_log(error)
				alert('Google AI が不調なようです。時間を空けて再実行してみてください。')
			})
			.finally(()=>{
				loader2.value = false
				document.getElementById('gemini_btn').disabled=false
				document.getElementById('gemini_seo_btn').disabled=false
			})
		}

		const set_elm_hi = (p_id,p_px) =>{
			SET_ELEM_HEIGHT(p_id,p_px)
		}

		const set_midasi = (p_midasi) =>{
			midasi.value = p_midasi
		}

		const post_sns = ref({'text':''})
		const timing = ref('')
		const sns_type = ref('SNS')
		const tag_param = computed(()=>{return String(post_sns.value.tag_disp).replaceAll("#", "")})
		const set_sns = (p_midasi) =>{
			let tag = ""
			post_sns.value = p_midasi
			post_sns.value.URL_line = encodeURIComponent(post_sns.value.URL+'ln')
			post_sns.value.tags.forEach((item)=>{
				tag += `${item},`
			})
			post_sns.value.tag_disp = tag.slice(0, -1)
			console_log(post_sns.value)
		}
		const product_url = ref(`${HTTP}product.php?id=`)

		const text_len = computed(()=>{return encodeURI(post_sns.value.text).split(/%..|./).length - 1;})
		const posting = () =>{
			loader2.value = true
			const params = new FormData();
			params.append(`tweet`, `${post_sns.value.text}`);
			params.append(`URL`, `${post_sns.value.URL}X`);
			params.append(`hash_tag`, ` ${post_sns.value.tag_disp}`);
			params.append(`hash`,p_hash);
			params.append(`csrf_token`, token)
	
			axios.post("tweet_as_shop.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
			.then((response) => {
				//console_log(response)
				console_log(response.data)
				token = response.data.csrf_create
				//console_log(obj)
				console_log('tweet_as_shop succsess')
				alert(response.data.MSG)
			})
			.catch((error)=>{
				token = response.data.csrf_create
				console_log('tweet_as_shop.php ERROR')
				console_log(error)
				alert('投稿失敗')
			})
			.finally(()=>{
				loader2.value = false
			})
	
		}

		onMounted(()=>{
			console_log(`onMounted : ${Where_to_use}`)
			if(Where_to_use==="shouhinMS.php"){
				//get_shouhinMS()
				//get_shouhinMS_newcd()
			}
			get_shouhinMS_online()
		})
	
		return{
			msg,
			loader,
			loader2,
			RTURL,
			mode,
			shouhin_table,
			shouhinMS,
			shouhinMS_pic,
			get_shouhinMS,
			uid,
			x_id,
			shouhinCD,
			shouhinNM,
			hash_tag,
			yagou,
			limited_cd,
			status,
			tanka,
			zei,
			midasi,
			info,
			haisou,
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
			set_shouhinNM,
			upd_status,
			disp,
			cg_mode,
			copy_target,
			copy_sns,
			open_product_page,
			get_AI_post,
			get_AI_seo,
			AI_answer,
			AI_answer_seo,
			tags_add,
			set_elm_hi,
			set_midasi,
			post_sns,
			sns_type,
			set_sns,
			product_url,
			posting,
			tag_param,
			text_len,
			timing,
			pic_sort_chk
		}
	}
});
