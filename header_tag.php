  <HEADER class='common_header' id='header'>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" style='padding:0;'>
      <div class="container common_header" <?php echo $_SESSION["h_color"];?>>
        <?php /*カラー変更されてる場合はロゴを表示しない*/
          if($_SESSION["h_color"]==="#f9d0d8" || empty($_SESSION["h_color"])){echo '<img src="img/icon-48x48.png" alt="Logo" width="48" height="48" class="d-inline-block align-text-top">';} 
        ?>
        <a class="navbar-brand alice-regular" href="index.php?key=<?php echo $_SESSION["user_hash"];?>"><h1 <?php echo $_SESSION["hf_color"];?>><?php echo TITLE.$_SESSION["yagou"];?></h1></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ms-5" id="navbarNav">
          <ul class="navbar-nav alice-regular" <?php echo $_SESSION["hf_color"];?>>
            <li class="nav-item">
              <a class="nav-link" href="index.php?key=<?php echo $_SESSION["user_hash"];?>" id='menu_home'>Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="shops.php" id='menu_Shops'>Shops</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="order_rireki.php" id='menu_rireki'>購入履歴</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="privacy_policy.php" id='menu_privacy'>プライバシーポリシー</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="toiawase.php" id='menu_toiawase'>特定商取引法に基づく表記</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </HEADER>
