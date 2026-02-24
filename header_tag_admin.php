<?php
  $login = $login ?? true;
  if($login){
    $top_url = "admin_menu.php?key=$user_hash";
  }else{
    $top_url = "admin_login.php?key=$user_hash";
  }
?>
  <HEADER class='common_header' id='admin_menu'>
    <nav class="navbar navbar-expand-xl bg-body-tertiary fixed-top" style='padding:0;'>
      <div class="container common_header">
        <img src="img/icon-48x48.png" alt="Logo" width="48" height="48" class="d-inline-block align-text-top">
        <!--<a class="navbar-brand alice-regular" href="admin_menu.php?key=<?php //echo $user_hash;?>"><h1>管理メニュー</h1></a>-->
        <a class="navbar-brand alice-regular" href="<?php echo $top_url;?>"><h1>管理メニュー</h1></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ms-5" id="navbarNav" >
          <ul class="navbar-nav alice-regular">
            <template v-for='(list,index) in menu' :key='list.name'>
              <li class="nav-item">
                <a class="nav-link" :href="list.url" :id='`menu_0${index}`'>{{list.name}}</a>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>
  </HEADER>
