<?php
/*
madelineproto o'rnatilga direktoriyada turishi kerak.


Foydalanuvchi botga youtube link tashlaydi.
Link olib boshqa boshqa serverda turgan videoni manzilini toadi.
qobilbek.ga sayti bu vazifani bajaryapdi bunda.
Videoni yunaltiruvchi user egasi yuklamoq botiga junatadi.
videoning caption xossasiga link tashlagan odamni chat_id sini qushadi

yuklamoq bot videoni qabul qiladi
qabul qilinga videodagi captionni olib captiondagi chat id ga junatadi
*/



$token = "bottoken";//telegram botdagi token

//telegram yuklanmalar olish
$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$text = $message->text;
$chat_id = $message->chat->id;
$file_id = $message->video->file_id;
$caption = $message->caption;
$yunaltiruvchi = 123;//yunaltiruvchining caht id si


function send($metod,$datas=[]){
    $datas['chat_id'] = $GLOBALS['chat_id'];
    $datas['parse_mode'] = "HTML";
    $token = $GLOBALS['token'];
    $url = "https://api.telegram.org/bot$token/".$metod;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}

function mad($api_url, $chat_id){
    if (!file_exists('madeline.php')) {
        copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
    }
    include 'madeline.php';
    
    $MadelineProto = new \danog\MadelineProto\API('session.madeline');
    $MadelineProto->start();
    
    $sentMessage = $MadelineProto->messages->sendMedia([
        'peer' => '@yuklamoq_bot',//botga user nomidan junatadi
        'media' => [
            '_' => 'inputMediaUploadedDocument',
            'file' => $api_url,
            'attributes' => [
                ['_' => 'documentAttributeVideo', 'round_message' => false, 'supports_streaming' => true]
            ]
        ],
        'message' => "$chat_id",//caption ga junatilishi kerak bulgan chat id ni yozadi
        'parse_mode' => 'Markdown'
    ]);
}

if ($chat_id != $yunaltiruvchi){//yunaltiruvchi userdan boshqasi bulsa vidoeni yuklaydi. yunaltiruvchi uchun ishlamaydi yuklash
    if (strlen($text) > 0){
        $api = file_get_contents("https://qobilbek.ga/Api/youtube/index.php?url=$text");
        $api = json_decode($api);
        $i = 0;
        //foreach bilan jadvaldagi urllardan keraklisini ajratib olyadi
        foreach($api->info as &$v){
            $api_url[$i] = $v->url;
            $i++;
        }
        $api_url = $api_url[0];
        mad($api_url, $chat_id);
    }
} else {
    file_get_contents("https://api.telegram.org/bot$token/sendVideo?chat_id=$caption&video=$file_id");
    //curl bilan yuklashda muammo buldi shunga file_get_contents ni ishlatdim
}
?>
