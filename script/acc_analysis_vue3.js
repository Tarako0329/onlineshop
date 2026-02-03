//const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
const acc_analysis = (Where_to_use,p_token,p_hash) => createApp({//サイト設定
  setup() {
    const shoplist = ref([])
    const an_type = ref('2')
    const tani = ref('d')  //範囲の単位
    const tani2 = ref() //集計対象：人or表示回数
    const from = ref(GET_KONGETU())
    const to = ref(GET_KONGETU())
    const ymlist = ref()
    const ylist = ref()
    const taishou_all = ref(false)

    const list = computed(()=>{
      if(tani.value === 'y'){
        return ylist.value
      }else{
        return ymlist.value
      }
    })

    watch([an_type,tani,from,to,taishou_all],()=>{
      if(an_type.value==='3'){
        if(tani.value==='d'){
          tani.value = 'm'
        }
      }
      get_acc_analysis()
    })

    //chartjs
		let graph_obj //chart object
    let graph_name = ''

    const get_graph_data = () => {
			console_log("get_graph_data : daikoumoku : " + an_type.value)
      let return_data
      if(an_type.value==='1'){
        console_log("1 start")
        graph_name = '新規／再訪 （人数）'
        return_data = {
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
        
        analysis_data.value.forEach((row)=>{
          return_data.labels.push(row.date)
          return_data.datasets[0].data.push(row.初訪問)
          return_data.datasets[1].data.push(row.再訪問)
        })
      }else if(an_type.value==='2'){
        console_log("2 start")
        graph_name = 'アクセス経路 （人数）'
        return_data = {
          labels:[]
          ,datasets:[
            {
              label : 'X.com'
              ,data : [],backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)',
            },{
              label : 'instagram'
              ,data : [],backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'
            },{
              label : 'facebook'
              ,data : [],backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'
            },{
              label : 'google'
              ,data : [],backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'
            },{
              label : 'その他'
              ,data : [],backgroundColor: 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'
            }
          ]
          //,barThickness:5
        }

        analysis_data.value.forEach((row)=>{
          return_data.labels.push(row.date)
          return_data.datasets[0].data.push(row.X)
          return_data.datasets[1].data.push(row.instagram)
          return_data.datasets[2].data.push(row.facebook)
          return_data.datasets[3].data.push(row.google)
          return_data.datasets[4].data.push(row.その他)
        })
        
      }else if(an_type.value==='3'){
        console_log("3 start")
        graph_name = 'ページ別 （人数）'
        return_data = {
          labels:[]
          ,datasets:[]
        }
        
        let shori_ym=''
        let index = 0
        let loop_cnt = -1
        analysis_data.value.forEach((row)=>{
          if(shori_ym!==row.date){
            return_data.labels.push(row.date)
            shori_ym = row.date
            index = 0
            loop_cnt = loop_cnt + 1
          }
          if(loop_cnt === 0){
            return_data.datasets.push({'label':row.shouhinNM,'data':[row.訪問者数],'backgroundColor':'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.8)'})
          }else{
            console_log(index)
            return_data.datasets[index].data.push(row.訪問者数)
          }
          index = index + 1
        })
        
      }
      //console_log("get_graph_data end")
      console_log(return_data)
			return return_data
		}


    // 画面サイズに合わせてフォントサイズを変更
    let juge_indexAxis = (window.innerWidth < 1000)?"y":"x"
    let juge_reverse = (window.innerWidth < 1000)?false:true
    window.addEventListener('resize', function() {
      const windowWidth = window.innerWidth;
      let new_val
      let new_val2
      if (windowWidth < 1000) {
          // スマホの場合
          //document.body.style.fontSize = '14px';
          console_log("スマホの場合")
          new_val = 'y'
          new_val2 = false
      } else {
          // PCの場合
          //document.body.style.fontSize = '16px';
          console_log("PCの場合")
          new_val = 'x'
          new_val2 = true
      }

      if(new_val !== juge_indexAxis){
        console_log("グラフリライト")
        juge_indexAxis = new_val
        juge_reverse = new_val2
        create_graph(document.getElementById('myChart'))
      }

    });


		const create_graph = (ctx) =>{
			console_log("create_graph : graph_data")
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
							text: graph_name
						},
					},
          maintainAspectRatio: false, //データに合わせて表エリアを調整する
					responsive: true,
          //indexAxis: 'y',
          indexAxis: juge_indexAxis,
          elements: {
            bar: {
              barThickness: 20 // 棒の幅を20pxに設定
            }
          },
          
					scales: {
            x: {
              //stacked: true,
              stacked: (an_type.value==='3')?false:true,
              ticks: {
                
              },
              reverse: juge_reverse // X 軸を逆順にする
              //max:30
						},
						y: {
              //stacked: true,
              stacked: (an_type.value==='3')?false:true,
              barPercentage: 0.9,
              ticks: {
                
              },
						}
					}
				}
			})      
		}

		
    const analysis_data = ref() //初回かリピーターか

    const get_acc_analysis = () => {
      const params = new FormData();
      params.append('an_type',an_type.value)
      params.append('from',from.value)
      params.append('to',to.value)
      params.append('tani',tani.value)
      params.append('taishou_all',taishou_all.value)

      axios.post("ajax_get_analysis.php",params, {headers: {'Content-Type': 'multipart/form-data'}})
      .then((response) => {
        console_log(response.data,'all')
        analysis_data.value = response.data
        if(an_type.value!=='4'){
          create_graph(document.getElementById('myChart'))
        }
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
      //ymlist作成
      axios.post("ajax_get_ymlist.php")
      .then((response) => {
        console_log(response.data)
        ymlist.value = response.data.ymlist
        ylist.value = response.data.ylist
        console_log('ajax_get_analysis succsess')
      })
      .catch((error)=>{
        console_log('ajax_get_analysis.php ERROR')
        console_log(error)
      })
      .finally(()=>{
      })

      get_acc_analysis()
      GET_USER2()
      .then((response)=>{
        console_log(response)
        shoplist.value = response.Users_online
      })
    })

    return {
      shoplist,
      analysis_data,
      an_type,
      tani,
      tani2,
      from,
      to,
      list,
      taishou_all,
    }
  }
})



