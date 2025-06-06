<!-- headタグの共通部分 -->
<?php
    if(EXEC_MODE<>'Product'){
        echo "<meta name='robots' content='noindex' />\r\n";
    }
    if(EXEC_MODE==='Product'){
        echo "<!-- Google tag (gtag.js) -->\r\n";
        echo "<script async src='https://www.googletagmanager.com/gtag/js?id=G-GYJFG67WPE'></script>\r\n";
        echo "<script>\r\n";
        echo "  window.dataLayer = window.dataLayer || [];\r\n";
        echo "  function gtag(){dataLayer.push(arguments);}\r\n";
        echo "  gtag('js', new Date());\r\n";
        echo "  gtag('config', 'G-GYJFG67WPE');\r\n";
        echo "</script>\r\n";
    }
?>
    <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
    <meta property="og:locale" content="ja_JP"  />
    <link rel='apple-touch-icon' href='apple-touch-icon.png'>

    <!--<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Zen+Antique&display=swap" rel="stylesheet">-->

    <!-- Bootstrap5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Bootstrap Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <!-- fontawesome -->
    <!--<link href="css/FontAwesome/6.1.1-web/css/all.css" rel="stylesheet">-->
    <!-- オリジナル CSS -->
    <!--サイト共通-->
    <link rel='stylesheet' href='css/style.css?<?php echo $time; ?>' >
    <link rel='stylesheet' href='css/style_color.css?<?php echo $time; ?>' >

    <!--Vue.js-->
    <!--<script src="https://unpkg.com/vue@next"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/vue@3.4.4"></script>
    <!--<script src="https://unpkg.com/vue-cookies@1.8.2/vue-cookies.js"></script>-->
    <!--<script type="importmap">
      {
        "imports": {
          "unheadvue": "https://cdn.jsdelivr.net/npm/@unhead/vue@1.11.11/+esm"
          ,"vue":"https://unpkg.com/vue@3/dist/vue.esm-browser.js"
        }
      }
    </script>-->

    <!--ajaxライブラリ-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script>axios.defaults.baseURL = <?php echo "'".ROOT_URL."'" ?>;</script>
	<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.js"></script>QRコードライブラリ-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/decimal.js/9.0.0/decimal.min.js"></script><!--小数演算ライブラリ-->

    <script>
        var KANKYO = <?php echo "'".EXEC_MODE."'" ;?>;
        const HTTP = <?php echo "'".ROOT_URL."'" ;?>;
        var COLOR_NO 
    </script>
    <script src="script/function.js?<?php echo $time; ?>"></script>
    <!--<script src="script/class.js?<?php //echo $time; ?>"></script>-->
    <script src="script/indexeddb.js?<?php echo $time; ?>"></script>


    <link rel='manifest' href='manifest.webmanifest'>
    <script>/*serviceWorker*/
        /*
        if('serviceWorker' in navigator){
        	navigator.serviceWorker.register('serviceworker.js').then(function(){
        		console_log("Service Worker is registered!!");
        	});
        }
        */
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('serviceworker.js')
                .then(registration => {
                    // 登録成功
                    console_log("Service Worker is registered!!");
                    
                    //serviceworker.js　の更新確認(bit単位で比較し相違があったら更新する。らしい)
                    registration.onupdatefound = function() {
                        console_log('Service Worker is Updated');
                        registration.update();
                    }
                })
                .catch(err => {
                    // 登録失敗
                    console_log("Service Worker is Oops!!");
            });
        }

        if(window.matchMedia('(display-mode: standalone)').matches){
            // ここにPWA環境下でのみ実行するコードを記述
        }
        //スマフォで:active :hover を有効に
        document.getElementsByTagName('html')[0].setAttribute('ontouchstart', '');
    </script>
