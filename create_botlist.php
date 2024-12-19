<?php

//botリストの作成
file_put_contents("bot_list.txt",file_get_contents('https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json'));
?>