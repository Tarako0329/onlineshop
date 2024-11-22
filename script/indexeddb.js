const dbName = 'PresentSelection';
const dbVersion=5;
const tableNM = (KANKYO!=='Product')?'cart_test':'cart'

const openReq  = indexedDB.open(dbName,dbVersion);
//　DB名を指定して接続。DBがなければ新規作成される。

openReq.onupgradeneeded = function(event){
//onupgradeneededは、DBのバージョン更新(DBの新規作成も含む)時のみ実行
  let db = event.target.result;
  
  db.createObjectStore(tableNM, {keyPath : 'id'})
  //IDD_Write('LocalParameters',[{id:'menu_color',No:'0'}])
  console_log('db upgrade');
}

openReq.onsuccess = function(event){
//onupgradeneededの後に実行。更新がない場合はこれだけ実行
  console_log('db open success');
  let DBCon = event.target.result;
  let dbVersion = DBCon.version;
  console_log(`version : ${dbVersion}`);

  // 接続を解除する
  DBCon.close();
}

openReq.onerror = function(event){
// 接続に失敗
  console_log('db open error');
}

const IDD_Write = (tbname,objs) =>{
  const openReq  = indexedDB.open(dbName,dbVersion);

  openReq.onsuccess = function(event){
    console_log('[IDD_Write]db open success');
    let DBCon = event.target.result;
    let transaction = DBCon.transaction(tbname, "readwrite");

    const objectStore = transaction.objectStore(tbname);
    objs.forEach((obj) => {
      const request = objectStore.put(obj);
      request.onsuccess = (event) => {
        // event.target.result === customer.ssn;
      };
    });

    transaction.oncomplete = (event) => {
      console_log("IDD_Write All done!");
    };
    
    transaction.onerror = (event) => {
      // エラー制御を忘れずに!
    };  
  }
}
const IDD_Delete = (tbname,key) =>{
  const openReq  = indexedDB.open(dbName,dbVersion);

  openReq.onsuccess = function(event){
    console_log('[IDD_Write]db open success');
    let DBCon = event.target.result;
    let transaction = DBCon.transaction(tbname, "readwrite");

    const objectStore = transaction.objectStore(tbname);
    const request = objectStore.delete(key);
    request.onsuccess = (event) => {
      // event.target.result === customer.ssn;
    };

    transaction.oncomplete = (event) => {
      console_log("IDD_Delete All done!");
    };
    
    transaction.onerror = (event) => {
      // エラー制御を忘れずに!
    };  
  }
}
const IDD_Delete_ALL = (tbname) =>{
  const openReq  = indexedDB.open(dbName,dbVersion);

  openReq.onsuccess = function(event){
    console_log('[IDD_Write]db open success');
    let DBCon = event.target.result;
    let transaction = DBCon.transaction(tbname, "readwrite");

    const objectStore = transaction.objectStore(tbname);
    const request = objectStore.clear(key);
    request.onsuccess = (event) => {
      // event.target.result === customer.ssn;
    };

    transaction.oncomplete = (event) => {
      console_log("IDD_Delete_ALL All done!");
    };
    
    transaction.onerror = (event) => {
      // エラー制御を忘れずに!
    };  
  }
}

const IDD_Read = (tbname,keyValue,callback) =>{
  const openReq  = indexedDB.open(dbName,dbVersion);
  
  openReq.onsuccess = function(event){
    console_log('[IDD_Read]db open success');
    let DBCon = event.target.result;
    let transaction = DBCon.transaction(tbname, "readonly");

    const objectStore = transaction.objectStore(tbname);
    let getReq = objectStore.get(keyValue);

    getReq.onsuccess = function(event){
      console_log(event.target.result); // {id : 'A1', name : 'test'}
      
      if(callback !== null){
        callback(event.target.result)
      }else{
        
      }
    }
    
    getReq.onerror = (event) => {
      // エラー制御を忘れずに!
    };
  }
}

const IDD_Read_All = (tbname,callback) =>{
  const openReq  = indexedDB.open(dbName,dbVersion);
  
  openReq.onsuccess = function(event){
    console_log('[IDD_Read]db open success');
    const DBCon = event.target.result;
    const transaction = DBCon.transaction(tbname, "readonly");

    const objectStore = transaction.objectStore(tbname);
    const getReq = objectStore.getAll();

    getReq.onsuccess = function(event){
      console_log(event.target.result); // {id : 'A1', name : 'test'}
      
      if(callback !== null){
        callback(event.target.result)
      }else{
        
      }
    }
    
    getReq.onerror = (event) => {
      // エラー制御を忘れずに!
    };
  }
}

//IDD_Write('LocalParameters',[{id:'menu_color',No:'1'}])
//IDD_Read('LocalParameters','menu_color')
