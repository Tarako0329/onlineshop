const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;

const admin_menu = (Where_to_use,p_token,user_hash) => createApp({//管理者メニュー
  setup() {
    const menu = ref([
      {name:'サイト設定',url : `configration.php?key=${user_hash}`},
      {name:'決済設定',url : `settlement.php?key=${user_hash}`},
      {name:'商品管理',url : `shouhinMS.php?key=${user_hash}`},
      {name:'受注管理',url : `order_management.php?key=${user_hash}`},
      {name:'発送サポート',url : `Unshipped_slip.php?key=${user_hash}`},
      {name:'広告宣伝',url : `sales_via_SNS.php?key=${user_hash}`},
      {name:'アクセス解析',url : `acc_analysis.php?key=${user_hash}`},
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
      }else if(Where_to_use==="sales_via_SNS.php"){
        document.getElementById("menu_05").classList.add("active");
      }else if(Where_to_use==="acc_analysis.php"){
        document.getElementById("menu_06").classList.add("active");
      }else if(Where_to_use==="seikyu_yotei.php"){
        document.getElementById("menu_07").classList.add("active");
      }else if(Where_to_use==="kiyaku.php"){
        document.getElementById("menu_08").classList.add("active");
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
const acc_analysis = (Where_to_use,p_token,p_hash) => createApp({//サイト設定
  setup() {
    const shoplist = ref([])


    //chartjs
		let graph_obj

    const get_graph_data = () => {
			console_log("get_graph_data : daikoumoku")
			let return_data = {
        labels:[]
        ,datasets:[
          {
            label : '初訪問'
            ,data : []
            ,backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'
          },{
            label : 'リピーター'
            ,data : []
            ,backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'
          }
        ]
      }
			//console_log(data)
			analysis_data.value.forEach((row)=>{
        return_data.labels.push(row.date)
				return_data.datasets[0].data.push(row.初訪問)
				return_data.datasets[1].data.push(row.再訪問)
			})
			return return_data
		}



		const create_graph = (ctx) =>{
			console_log("create_graph : graph_data")
			/*
			const graph_data = {
				labels    : readdata_summary.value.label
				,datasets : get_graph_data(open_fil.value)
			}
      */
			if(graph_obj){
				graph_obj.destroy()
			}

			graph_obj = new Chart(ctx, {
				type : 'bar'
				//,data: graph_data
				,data: get_graph_data()
				,options: {
					plugins: {
						title: {
							display: true,
							text: "sample"
						},
					},
					responsive: true,
					scales: {
						x: {
							stacked: true,
						},
						y: {
							stacked: true
						}
					}
				}
			})      
		}

		
    const analysis_data = ref() //初回かリピーターか

    const get_acc_analysis = () => {
      const params = new FormData();

      axios.post("ajax_get_analysis.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response) => {
        console_log(response)
        analysis_data.value = response.data
        //console_log(get_graph_data())
        create_graph(document.getElementById('myChart'))
        console_log('ajax_get_analysis succsess')
      })
      .catch((error)=>{
        console_log('ajax_get_analysis.php ERROR')
        console_log(error)
      })
      .finally(()=>{
      })
    }

    onMounted(()=>{
      get_acc_analysis()
      GET_USER2()
      .then((response)=>{
        console_log(response)
        shoplist.value = response.Users_online
      })
      document.getElementById("menu_Shops").classList.add("active");
    })

    return {
      shoplist,
      analysis_data,
    }
  }
})



