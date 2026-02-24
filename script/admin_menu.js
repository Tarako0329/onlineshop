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
      {name:'レビュー管理',url : `review_management.php?key=${user_hash}`},
      {name:'問合せ管理',url : `Q_and_A_mgr.php?key=${user_hash}`},
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
      }else if(Where_to_use==="review_management.php"){
        document.getElementById("menu_07").classList.add("active");
      }else if(Where_to_use==="Q_and_A_mgr.php"){
        document.getElementById("menu_08").classList.add("active");
      }else if(Where_to_use==="seikyu_yotei.php"){
        document.getElementById("menu_09").classList.add("active");
      }else if(Where_to_use==="kiyaku.php"){
        document.getElementById("menu_010").classList.add("active");
      }else if(Where_to_use==="admin_kiyaku.php"){
        menu.value = []//ログイン画面なのでメニューなし
      }else if(Where_to_use==="admin_pbPolicy.php"){
        menu.value = []//ログイン画面なのでメニューなし
      }else if(Where_to_use==="admin_login.php"){
        menu.value = []//ログイン画面なのでメニューなし
      }else{
      }
    })

    return{
      menu,
    }
  }
})

