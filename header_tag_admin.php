  <HEADER class='common_header' id='admin_menu'>
    <!--<img src='img/icon-48x48.png' id="scrollspyHeading">
    <h1 class='mt-3 alice-regular'><a href="admin_menu.php?key=<?php //echo $user_hash;?>"><?php //echo TITLE;?></a></h1>-->
    <nav class="navbar navbar-expand-xl bg-body-tertiary fixed-top" style='padding:0;'>
      <div class="container common_header">
        <img src="img/icon-48x48.png" alt="Logo" width="48" height="48" class="d-inline-block align-text-top">
        <a class="navbar-brand alice-regular" href="admin_menu.php?key=<?php echo $user_hash;?>"><h1>管理メニュー</h1></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ms-5" id="navbarNav">
          <ul class="navbar-nav alice-regular">
            <template v-for='(list,index) in menu' :key='list.name'>
              <li class="nav-item">
                <!--<a :class="nav_class[index]" :href="list.url" :id='`menu_0${index}`'>{{list.name}}</a>-->
                <a class="nav-link" :href="list.url" :id='`menu_0${index}`'>{{list.name}}</a>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>
  </HEADER>
