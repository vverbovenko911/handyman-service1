<?php

use \Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

function authSession($force=false){
    $session = new \App\Models\User;
    if($force){
        $user = \Auth::user()->getRoleNames();
        \Session::put('auth_user',$user);
        $session = \Session::get('auth_user');
        return $session;
    }
    if(\Session::has('auth_user')){
        $session = \Session::get('auth_user');
    }else{
        $user = \Auth::user();
        \Session::put('auth_user',$user);
        $session = \Session::get('auth_user');
    }
    return $session;
}

function comman_message_response( $message, $status_code = 200){
	return response()->json( [ 'message' => $message ], $status_code );
}

function comman_custom_response( $response, $status_code = 200 ){
    return response()->json($response,$status_code);
}

function checkMenuRoleAndPermission($menu){
    if (\Auth::check()) {
        if ($menu->data('role') == null && auth()->user()->hasRole('admin')) {
            return true;
        }

        if($menu->data('permission') == null && $menu->data('role') == null) {
            return true;
        }

        if($menu->data('role') != null) {
            if(is_array($menu->data('role'))){
                if(auth()->user()->hasAnyRole($menu->data('role'))) {
                    return true;
                }
            }
            if(auth()->user()->hasAnyRole($menu->data('role'))){
                return true;
            }
        }

        if($menu->data('permission') != null) {
            if(is_array($menu->data('permission'))){
                if(auth()->user()->hasAnyPermission($menu->data('permission'))){
                    return true;
                }

            }
            if(auth()->user()->can($menu->data('permission')) ) {
                return true;
            }

        }
    }

    return false;
}

function checkRolePermission($role,$permission){
    try{
        if($role->hasPermissionTo($permission)){
            return true;
        }
        return false;
    }catch (Exception $e){
        return false;
    }
}

function demoUserPermission(){
    if(\Auth::user()->hasAnyRole(['demo_admin'])){
        return true;
    }else{
        return false;
    }
}

function getSingleMedia($model, $collection = 'profile_image', $skip=true   ){
    if (!\Auth::check() && $skip) {
        return asset('images/user/user.png');
    }
    $media = null;
    if ($model !== null) {
        $media = $model->getFirstMedia($collection);
    }

    if (getFileExistsCheck($media)) {
        return $media->getFullUrl();
    }else{

        switch ($collection) {
            case 'image_icon':
                $media = asset('images/user/user.png');
                break;
            case 'profile_image':
                $media = asset('images/user/user.png');
                break;
            case 'service_attachment':
                $media = asset('images/default.png');
                break;
            case 'site_logo':
                $media = asset('images/logo.png');
                break;
            case 'site_favicon':
                $media = asset('images/favicon.png');
                break;
            case 'app_image':
                $media = asset('images/frontend/mb-serv-1.png');
                break;
            case 'app_image_full':
                $media = asset('images/frontend/mb-serv-full.png');
                break;
            case 'footer_logo':
                $media = asset('landing-images/logo/logo.png');
                break;
            case 'logo':
                $media = asset('images/logo.png');
                break;
            case 'favicon':
                $media = asset('images/favicon.png');
                break;
            case 'loader':
                $media = asset('images/loader.gif');
                break;
            default:
                $media = asset('images/default.png');
                break;
        }
        return $media;
    }
}

function getFileExistsCheck($media){
    $mediaCondition = false;

    if($media) {
        if($media->disk == 'public') {
            $mediaCondition = file_exists($media->getPath());
        } else {
            $mediaCondition = \Storage::disk($media->disk)->exists($media->getPath());
        }
    }

    return $mediaCondition;
}

function storeMediaFile($model,$file,$name){
    if($file) {
        if( !in_array($name, ['service_attachment','package_attachment','blog_attachment','section5_attachment'])){
            $model->clearMediaCollection($name);
        }
        if (is_array($file)){
            foreach ($file as $key => $value){
                $model->addMedia($value)->toMediaCollection($name);
            }
        }else{
            $model->addMedia($file)->toMediaCollection($name);
        }
    }

    return true;
}

function getAttachments($attchments){
    $files = [];
    if (count($attchments) > 0) {
        foreach ($attchments as $attchment) {
            if (getFileExistsCheck($attchment)) {
                array_push($files, $attchment->getFullUrl());
            }
        }
    }

    return $files;
}

function getAttachmentArray($attchments){
    $files = [];
    if (count($attchments) > 0) {
        foreach ($attchments as $attchment) {
            if (getFileExistsCheck($attchment)) {
                $file = [
                    'id' => $attchment->id,
                    'url'=> $attchment->getFullUrl()
                ];
                array_push($files, $file);
            }
        }
    }

    return $files;
}

function getMediaFileExit($model, $collection = 'profile_image'){
    if($model==null){
        return asset('images/user/user.png');;
    }

    $media = $model->getFirstMedia($collection);

    return getFileExistsCheck($media);
}

function formatOffset($offset){
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int) abs($hours);
    $minutes = (int) abs($remainder / 60);

    if ($hour == 0 and $minutes == 0) {
        $sign = ' ';
    }
    return 'GMT' . $sign . str_pad($hour, 2, '0', STR_PAD_LEFT)
        . ':' . str_pad($minutes, 2, '0');
}

function settingSession($type='get'){
    if(\Session::get('setting_data') == ''){
        $type='set';
    }
    switch ($type){
        case "set" :
            $settings = \App\Models\AppSetting::first();
            \Session::put('setting_data',$settings);
            break;
        default :
            break;
    }
    return \Session::get('setting_data');
}

function imageSession($type='get'){
    if(\Session::get('images_data') == ''){
        $type='set';
    }
    switch ($type){
        case "set" :
            $settings = \App\Models\Setting::where('type','theme-setup')->where('key','theme-setup')->first();
            \Session::put('images_data',$settings);
            break;
        default :
            break;
    }
    return \Session::get('images_data');
}

function sitesetupSession($type='get'){
    if(\Session::get('setup_data') == ''){
        $type='set';
    }
    switch ($type){
        case "set" :
            $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $settings = $sitesetup ? json_decode($sitesetup->value) : null;
            if(!empty($settings)){
                \Session::put('setup_data',$settings);
            }
            
            break;
        default :
            break;
    }
    return \Session::get('setup_data');
}

function envChanges($type,$value){
    $path = base_path('.env');

    $checkType = $type.'="';
    if(strpos($value,' ') || strpos(file_get_contents($path),$checkType) || preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $value)){
        $value = '"'.$value.'"';
    }

    $value = str_replace('\\', '\\\\', $value);

    if (file_exists($path)) {
        $typeValue = env($type);

        if(strpos(env($type),' ') || strpos(file_get_contents($path),$checkType)){
            $typeValue = '"'.env($type).'"';
        }

        file_put_contents($path, str_replace(
            $type.'='.$typeValue, $type.'='.$value, file_get_contents($path)
        ));

        $onesignal = collect(config('constant.ONESIGNAL'))->keys();

        $checkArray = \Arr::collapse([$onesignal,['DEFAULT_LANGUAGE']]);


        if( in_array( $type ,$checkArray) ){
            if(env($type) === null){
                file_put_contents($path,"\n".$type.'='.$value ,FILE_APPEND);
            }
        }
    }
}

function getPriceFormat($price){
    $price = (double)$price;

    $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
    $sitesetupdata = $sitesetup ? json_decode($sitesetup->value) : null;
    $currencyId = optional($sitesetupdata)->default_currency;
    $currency_position = optional($sitesetupdata)->currency_position;
    $afterdecimalpoint = optional($sitesetupdata)->digitafter_decimal_point;
    $country = App\Models\Country::find($currencyId);

    $symbol = '$';
    if (!empty($country)) {
        $symbol = $country->symbol;
    }

    $position = 'left';
    if( !empty($currency_position) ){
        $position = $currency_position;
    }

    if ($position == 'left') {
        $price = $symbol."".number_format((float)$price,$afterdecimalpoint,'.','');
    } else {
        $price = number_format((float)$price,$afterdecimalpoint,'.','')."".$symbol;
    }

    return $price;
}

function currency_data(){

    $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
    $sitesetupdata = $sitesetup ? json_decode($sitesetup->value) : null;
    $currencyId = optional($sitesetupdata)->default_currency;
    $currency_position = optional($sitesetupdata)->currency_position;
    $country = App\Models\Country::find($currencyId);

    $symbol = '$';
    if (!empty($country)) {
        $symbol = $country->symbol;
    }
     $position = 'left';
    if( !empty($currency_position) ){
        $position = $currency_position;
    }

    $data = [
        'currency_symbol' => $symbol,
        'currency_position' => $position,
    ];

    return  $data;
}

function payment_status(){

    return [
        'pending' => __('messages.pending'),
        'paid' => __('messages.paid'),
        'failed' => __('messages.failed'),
        'refunded' => __('messages.refunded')
    ];
}

function timeZoneList(){
    $list = \DateTimeZone::listAbbreviations();
    $idents = \DateTimeZone::listIdentifiers();

    $data = $offset = $added = array();
    foreach ($list as $abbr => $info) {
        foreach ($info as $zone) {
            if (!empty($zone['timezone_id']) and !in_array($zone['timezone_id'], $added) and in_array($zone['timezone_id'], $idents)) {

                $z = new \DateTimeZone($zone['timezone_id']);
                $c = new \DateTime(null, $z);
                $zone['time'] = $c->format('H:i a');
                $offset[] = $zone['offset'] = $z->getOffset($c);
                $data[] = $zone;
                $added[] = $zone['timezone_id'];
            }
        }
    }

    array_multisort($offset, SORT_ASC, $data);
    $options = array();
    foreach ($data as $key => $row) {

        $options[$row['timezone_id']] = $row['time'] . ' - ' . formatOffset($row['offset'])  . ' ' . $row['timezone_id'];
    }
    $options['America/Sao_Paulo'] = '3:00 pm -  GMT-03:00 America/Sao_Paulo';
    return $options;
}

function dateFormatList() {
    return [
        'Y-m-d' => date('Y-m-d'),
        'm-d-Y' => date('m-d-Y'),
        'd-m-Y' => date('d-m-Y'),
        'd/m/Y' => date('d/m/Y'),
        'm/d/Y' => date('m/d/Y'),
        'Y/m/d' => date('Y/m/d'),
        'Y.m.d' => date('Y.m.d'),
        'd.m.Y' => date('d.m.Y'),
        'm.d.Y' => date('m.d.Y'),
        'jS M Y' => date('jS M Y'),
        'M jS Y' => date('M jS Y'),
        'D, M d, Y' => date('D, M d, Y'),
        'D, d M, Y' => date('D, d M, Y'),
        'D, M jS Y' => date('D, M jS Y'),
        'D, jS M Y' => date('D, jS M Y'),
        'F j, Y' => date('F j, Y'),
        'd F, Y' => date('d F, Y'),
        'jS F, Y' => date('jS F, Y'),
        'l jS F Y' => date('l jS F Y'),
        'l, F j, Y' => date('l, F j, Y'),

    ];
}

function getTimeInFormat($format) {
    $now = new DateTime();
    $hours = $now->format('H');
    $minutes = $now->format('i');
    $seconds = $now->format('s');
    $milliseconds = $now->format('v');
    $totalSecondsSinceMidnight = ($hours * 3600) + ($minutes * 60) + $seconds;

    switch ($format) {
        case "H:i":
            return "$hours:$minutes";
        case "H:i:s":
            return "$hours:$minutes:$seconds";
        case "g:i A":
            $ampm = $hours >= 12 ? 'PM' : 'AM';
            $formattedHours = $hours % 12 || 12;
            return "$formattedHours:$minutes $ampm";
        case "H:i:s T":
            return "$hours:$minutes:$seconds UTC";
        case "H:i:s.v":
            return "$hours:$minutes:$seconds.$milliseconds";
        case "U":
            return $now->getTimestamp();
        case "u":
            return $milliseconds * 1000;
        case "G.i":
            return $hours + $minutes / 60;
        case "@BMT":
            $swatchBeat = floor($totalSecondsSinceMidnight / 86.4);
            return "@{$swatchBeat}BMT";
        default:
            return "Invalid format";
    }
}

function timeFormatList() {
    $timeFormats = [
        "H:i",
        "H:i:s",
        "g:i A",
        "H:i:s T",
        "H:i:s.v",
        "U",
        "u",
        "G.i",
        "@BMT"
    ];

    return array_map(function ($format) {
        return ['format' => $format, 'time' => getTimeInFormat($format)];
    }, $timeFormats);
}

function dateAgoFormate($date,$type2=''){
    if($date==null || $date=='0000-00-00 00:00:00'){
        return '-';
    }

    $diff_time1= \Carbon\Carbon::createFromTimeStamp(strtotime($date))->diffForHumans();
    $datetime = new \DateTime($date);
    $la_time = new \DateTimeZone(\Auth::check() ? \Auth::user()->time_zone ?? 'UTC' : 'UTC');
    $datetime->setTimezone($la_time);
    $diff_date= $datetime->format('Y-m-d H:i:s');

    $diff_time= \Carbon\Carbon::parse($diff_date)->isoFormat('LLL');

    if($type2 != ''){
        return $diff_time;
    }

    return $diff_time1 .' on '.$diff_time;
}

function timeAgoFormate($date){
    if($date==null){
        return '-';
    }

    $diff_time= \Carbon\Carbon::createFromTimeStamp(strtotime($date))->diffForHumans();

    return $diff_time;
}

function duration($start ='' , $end='' ,$type = ''){
    $start = \Carbon\Carbon::parse($start);
    $end = \Carbon\Carbon::parse($end);

    if($type){
        $diff_in_minutes = $start->diffInMinutes($end);
        return $diff_in_minutes;
    }else{
        $diff = $start->diff($end);
        return $diff->format('%H:%I');
    }
}

function removeArrayValue($array = [],$find){
    foreach (array_keys($array, $find) as $key) {
        unset($array[$key]);
    }

    return array_values($array);
}

function handymanNames($collection){
    return $collection->mapWithKeys(function ($item) {
        return [$item->handyman_id => optional($item->handyman)->display_name];
    })->values()->implode(',');
}

function languagesArray($ids = []){
    $language = [
        [ 'title' => 'Abkhaz' , 'id' => 'ab'],
        [ 'title' => 'Afar' , 'id' => 'aa'],
        [ 'title' => 'Afrikaans' , 'id' => 'af'],
        [ 'title' => 'Akan' , 'id' => 'ak'],
        [ 'title' => 'Albanian' , 'id' => 'sq'],
        [ 'title' => 'Amharic' , 'id' => 'am'],
        [ 'title' => 'Arabic' , 'id' => 'ar'],
        [ 'title' => 'Aragonese' , 'id' => 'an'],
        [ 'title' => 'Armenian' , 'id' => 'hy'],
        [ 'title' => 'Assamese' , 'id' => 'as'],
        [ 'title' => 'Avaric' , 'id' => 'av'],
        [ 'title' => 'Avestan' , 'id' => 'ae'],
        [ 'title' => 'Aymara' , 'id' => 'ay'],
        [ 'title' => 'Azerbaijani' , 'id' => 'az'],
        [ 'title' => 'Bambara' , 'id' => 'bm'],
        [ 'title' => 'Bashkir' , 'id' => 'ba'],
        [ 'title' => 'Basque' , 'id' => 'eu'],
        [ 'title' => 'Belarusian' , 'id' => 'be'],
        [ 'title' => 'Bengali' , 'id' => 'bn'],
        [ 'title' => 'Bihari' , 'id' => 'bh'],
        [ 'title' => 'Bislama' , 'id' => 'bi'],
        [ 'title' => 'Bosnian' , 'id' => 'bs'],
        [ 'title' => 'Breton' , 'id' => 'br'],
        [ 'title' => 'Bulgarian' , 'id' => 'bg'],
        [ 'title' => 'Burmese' , 'id' => 'my'],
        [ 'title' => 'Catalan; Valencian' , 'id' => 'ca'],
        [ 'title' => 'Chamorro' , 'id' => 'ch'],
        [ 'title' => 'Chechen' , 'id' => 'ce'],
        [ 'title' => 'Chichewa; Chewa; Nyanja' , 'id' => 'ny'],
        [ 'title' => 'Chinese' , 'id' => 'zh'],
        [ 'title' => 'Chuvash' , 'id' => 'cv'],
        [ 'title' => 'Cornish' , 'id' => 'kw'],
        [ 'title' => 'Corsican' , 'id' => 'co'],
        [ 'title' => 'Cree' , 'id' => 'cr'],
        [ 'title' => 'Croatian' , 'id' => 'hr'],
        [ 'title' => 'Czech' , 'id' => 'cs'],
        [ 'title' => 'Danish' , 'id' => 'da'],
        [ 'title' => 'Divehi; Dhivehi; Maldivian;' , 'id' => 'dv'],
        [ 'title' => 'Dutch' , 'id' => 'nl'],
        [ 'title' => 'English' , 'id' => 'en'],
        [ 'title' => 'Esperanto' , 'id' => 'eo'],
        [ 'title' => 'Estonian' , 'id' => 'et'],
        [ 'title' => 'Ewe' , 'id' => 'ee'],
        [ 'title' => 'Faroese' , 'id' => 'fo'],
        [ 'title' => 'Fijian' , 'id' => 'fj'],
        [ 'title' => 'Finnish' , 'id' => 'fi'],
        [ 'title' => 'French' , 'id' => 'fr'],
        [ 'title' => 'Fula; Fulah; Pulaar; Pular' , 'id' => 'ff'],
        [ 'title' => 'Galician' , 'id' => 'gl'],
        [ 'title' => 'Georgian' , 'id' => 'ka'],
        [ 'title' => 'German' , 'id' => 'de'],
        [ 'title' => 'Greek, Modern' , 'id' => 'el'],
        [ 'title' => 'Guaraní' , 'id' => 'gn'],
        [ 'title' => 'Gujarati' , 'id' => 'gu'],
        [ 'title' => 'Haitian; Haitian Creole' , 'id' => 'ht'],
        [ 'title' => 'Hausa' , 'id' => 'ha'],
        [ 'title' => 'Hebrew (modern)' , 'id' => 'he'],
        [ 'title' => 'Herero' , 'id' => 'hz'],
        [ 'title' => 'Hindi' , 'id' => 'hi'],
        [ 'title' => 'Hiri Motu' , 'id' => 'ho'],
        [ 'title' => 'Hungarian' , 'id' => 'hu'],
        [ 'title' => 'Interlingua' , 'id' => 'ia'],
        [ 'title' => 'Indonesian' , 'id' => 'id'],
        [ 'title' => 'Interlingue' , 'id' => 'ie'],
        [ 'title' => 'Irish' , 'id' => 'ga'],
        [ 'title' => 'Igbo' , 'id' => 'ig'],
        [ 'title' => 'Inupiaq' , 'id' => 'ik'],
        [ 'title' => 'Ido' , 'id' => 'io'],
        [ 'title' => 'Icelandic' , 'id' => 'is'],
        [ 'title' => 'Italian' , 'id' => 'it'],
        [ 'title' => 'Inuktitut' , 'id' => 'iu'],
        [ 'title' => 'Japanese' , 'id' => 'ja'],
        [ 'title' => 'Javanese' , 'id' => 'jv'],
        [ 'title' => 'Kalaallisut, Greenlandic' , 'id' => 'kl'],
        [ 'title' => 'Kannada' , 'id' => 'kn'],
        [ 'title' => 'Kanuri' , 'id' => 'kr'],
        [ 'title' => 'Kashmiri' , 'id' => 'ks'],
        [ 'title' => 'Kazakh' , 'id' => 'kk'],
        [ 'title' => 'Khmer' , 'id' => 'km'],
        [ 'title' => 'Kikuyu, Gikuyu' , 'id' => 'ki'],
        [ 'title' => 'Kinyarwanda' , 'id' => 'rw'],
        [ 'title' => 'Kirghiz, Kyrgyz' , 'id' => 'ky'],
        [ 'title' => 'Komi' , 'id' => 'kv'],
        [ 'title' => 'Kongo' , 'id' => 'kg'],
        [ 'title' => 'Korean' , 'id' => 'ko'],
        [ 'title' => 'Kurdish' , 'id' => 'ku'],
        [ 'title' => 'Kwanyama, Kuanyama' , 'id' => 'kj'],
        [ 'title' => 'Latin' , 'id' => 'la'],
        [ 'title' => 'Luxembourgish, Letzeburgesch' , 'id' => 'lb'],
        [ 'title' => 'Luganda' , 'id' => 'lg'],
        [ 'title' => 'Limburgish, Limburgan, Limburger' , 'id' => 'li'],
        [ 'title' => 'Lingala' , 'id' => 'ln'],
        [ 'title' => 'Lao' , 'id' => 'lo'],
        [ 'title' => 'Lithuanian' , 'id' => 'lt'],
        [ 'title' => 'Luba-Katanga' , 'id' => 'lu'],
        [ 'title' => 'Latvian' , 'id' => 'lv'],
        [ 'title' => 'Manx' , 'id' => 'gv'],
        [ 'title' => 'Macedonian' , 'id' => 'mk'],
        [ 'title' => 'Malagasy' , 'id' => 'mg'],
        [ 'title' => 'Malay' , 'id' => 'ms'],
        [ 'title' => 'Malayalam' , 'id' => 'ml'],
        [ 'title' => 'Maltese' , 'id' => 'mt'],
        [ 'title' => 'Māori' , 'id' => 'mi'],
        [ 'title' => 'Marathi (Marāṭhī)' , 'id' => 'mr'],
        [ 'title' => 'Marshallese' , 'id' => 'mh'],
        [ 'title' => 'Mongolian' , 'id' => 'mn'],
        [ 'title' => 'Nauru' , 'id' => 'na'],
        [ 'title' => 'Navajo, Navaho' , 'id' => 'nv'],
        [ 'title' => 'Norwegian Bokmål' , 'id' => 'nb'],
        [ 'title' => 'North Ndebele' , 'id' => 'nd'],
        [ 'title' => 'Nepali' , 'id' => 'ne'],
        [ 'title' => 'Ndonga' , 'id' => 'ng'],
        [ 'title' => 'Norwegian Nynorsk' , 'id' => 'nn'],
        [ 'title' => 'Norwegian' , 'id' => 'no'],
        [ 'title' => 'Nuosu' , 'id' => 'ii'],
        [ 'title' => 'South Ndebele' , 'id' => 'nr'],
        [ 'title' => 'Occitan' , 'id' => 'oc'],
        [ 'title' => 'Ojibwe, Ojibwa' , 'id' => 'oj'],
        [ 'title' => 'Oromo' , 'id' => 'om'],
        [ 'title' => 'Oriya' , 'id' => 'or'],
        [ 'title' => 'Ossetian, Ossetic' , 'id' => 'os'],
        [ 'title' => 'Panjabi, Punjabi' , 'id' => 'pa'],
        [ 'title' => 'Pāli' , 'id' => 'pi'],
        [ 'title' => 'Persian' , 'id' => 'fa'],
        [ 'title' => 'Polish' , 'id' => 'pl'],
        [ 'title' => 'Pashto, Pushto' , 'id' => 'ps'],
        [ 'title' => 'Portuguese' , 'id' => 'pt'],
        [ 'title' => 'Quechua' , 'id' => 'qu'],
        [ 'title' => 'Romansh' , 'id' => 'rm'],
        [ 'title' => 'Kirundi' , 'id' => 'rn'],
        [ 'title' => 'Romanian, Moldavian, Moldovan' , 'id' => 'ro'],
        [ 'title' => 'Russian' , 'id' => 'ru'],
        [ 'title' => 'Sanskrit (Saṁskṛta)' , 'id' => 'sa'],
        [ 'title' => 'Sardinian' , 'id' => 'sc'],
        [ 'title' => 'Sindhi' , 'id' => 'sd'],
        [ 'title' => 'Northern Sami' , 'id' => 'se'],
        [ 'title' => 'Samoan' , 'id' => 'sm'],
        [ 'title' => 'Sango' , 'id' => 'sg'],
        [ 'title' => 'Serbian' , 'id' => 'sr'],
        [ 'title' => 'Scottish Gaelic; Gaelic' , 'id' => 'gd'],
        [ 'title' => 'Shona' , 'id' => 'sn'],
        [ 'title' => 'Sinhala, Sinhalese' , 'id' => 'si'],
        [ 'title' => 'Slovak' , 'id' => 'sk'],
        [ 'title' => 'Slovene' , 'id' => 'sl'],
        [ 'title' => 'Somali' , 'id' => 'so'],
        [ 'title' => 'Southern Sotho' , 'id' => 'st'],
        [ 'title' => 'Spanish; Castilian' , 'id' => 'es'],
        [ 'title' => 'Sundanese' , 'id' => 'su'],
        [ 'title' => 'Swahili' , 'id' => 'sw'],
        [ 'title' => 'Swati' , 'id' => 'ss'],
        [ 'title' => 'Swedish' , 'id' => 'sv'],
        [ 'title' => 'Tamil' , 'id' => 'ta'],
        [ 'title' => 'Telugu' , 'id' => 'te'],
        [ 'title' => 'Tajik' , 'id' => 'tg'],
        [ 'title' => 'Thai' , 'id' => 'th'],
        [ 'title' => 'Tigrinya' , 'id' => 'ti'],
        [ 'title' => 'Tibetan Standard, Tibetan, Central' , 'id' => 'bo'],
        [ 'title' => 'Turkmen' , 'id' => 'tk'],
        [ 'title' => 'Tagalog' , 'id' => 'tl'],
        [ 'title' => 'Tswana' , 'id' => 'tn'],
        [ 'title' => 'Tonga (Tonga Islands)' , 'id' => 'to'],
        [ 'title' => 'Turkish' , 'id' => 'tr'],
        [ 'title' => 'Tsonga' , 'id' => 'ts'],
        [ 'title' => 'Tatar' , 'id' => 'tt'],
        [ 'title' => 'Twi' , 'id' => 'tw'],
        [ 'title' => 'Tahitian' , 'id' => 'ty'],
        [ 'title' => 'Uighur, Uyghur' , 'id' => 'ug'],
        [ 'title' => 'Ukrainian' , 'id' => 'uk'],
        [ 'title' => 'Urdu' , 'id' => 'ur'],
        [ 'title' => 'Uzbek' , 'id' => 'uz'],
        [ 'title' => 'Venda' , 'id' => 've'],
        [ 'title' => 'Vietnamese' , 'id' => 'vi'],
        [ 'title' => 'Volapük' , 'id' => 'vo'],
        [ 'title' => 'Walloon' , 'id' => 'wa'],
        [ 'title' => 'Welsh' , 'id' => 'cy'],
        [ 'title' => 'Wolof' , 'id' => 'wo'],
        [ 'title' => 'Western Frisian' , 'id' => 'fy'],
        [ 'title' => 'Xhosa' , 'id' => 'xh'],
        [ 'title' => 'Yiddish' , 'id' => 'yi'],
        [ 'title' => 'Yoruba' , 'id' => 'yo'],
        [ 'title' => 'Zhuang, Chuang' , 'id' => 'za']
    ];
    if(!empty($ids))
    {
        $language = collect($language)->whereIn('id',$ids)->values();
    }
    return $language;
}

function flattenToMultiDimensional(array $array, $delimiter = '.'){
    $result = [];
    foreach ($array as $notations => $value) {
        // extract keys
        $keys = explode($delimiter, $notations);
        // reverse keys for assignments
        $keys = array_reverse($keys);

        // set initial value
        $lastVal = $value;
        foreach ($keys as $key) {
            // wrap value with key over each iteration
            $lastVal = [
                $key => $lastVal
            ];
        }

        // merge result
        $result = array_merge_recursive($result, $lastVal);
    }

    return $result;
}

// function createLangFile($lang=''){
//     $langDir = resource_path().'/lang/';
//     $enDir = $langDir.'en';
//     $currentLang = $langDir . $lang;
//     if(!File::exists($currentLang)){
//        File::makeDirectory($currentLang);
//        File::copyDirectory($enDir,$currentLang);
//     }
// }
function createLangFile($languages = []) {
    $langDir = resource_path('lang/');
    $enDir = $langDir . 'en';
    foreach ($languages as $lang) {
        $currentLangDir = $langDir . $lang;
        if (!File::exists($currentLangDir)) {
            File::makeDirectory($currentLangDir, 0755, true); 
            File::copyDirectory($enDir, $currentLangDir);
        }
    }
}
function deleteLangFile($selectedLanguages) {
    $langDir = resource_path('lang/');
    $allDirs = File::directories($langDir);

    foreach ($allDirs as $dir) {
        $dirName = basename($dir);
        if (!in_array($dirName, $selectedLanguages)) {
            File::deleteDirectory($dir);
        }
    }
}

function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return sprintf($format, 0, 0);
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

function getSettingKeyValue($key="",$radius_type=""){
    $setting_data = \App\Models\Setting::where('key',$key)->first();
    $radious_distance = $setting_data ? json_decode($setting_data->value) : null;
    $radious = $radious_distance->radious;
    $distance_type = $radious_distance->distance_type;

    if($radious_distance != null)
    {
        switch ($radius_type) {
            case 'distance_type':
                return $distance_type;
            case 'radious':
                return $radious;
            default:
                return getDefaultSetting($radius_type);
        }
    } else{

        switch ($radius_type) {
            case 'distance_type':
                return 'km';
                break;
            case 'radious':
                return 50;
                break;
            default:
                break;
        }

    }
}

function countUnitvalue($unit){
   switch ($unit) {
       case 'mile':
           return 3956;
           break;
       default:
           return 6371;
           break;
   }
}

function imageExtention($media){
    $extention = null;
    if($media != null){
        $path_info = pathinfo($media);
        $extention = $path_info['extension'];
    }
    return $extention;
}

function verify_provider_document($provider_id){
    $documents = \App\Models\Documents::where('is_required',1)->where('status', 1)->withCount([
        'providerDocument',
        'providerDocument as is_verified_document' => function ($query) use($provider_id) {
            $query->where('is_verified', 1)->where('provider_id', $provider_id);
        }])
    ->get();

    $is_verified = $documents->where('is_verified_document', 1);

    if(count($documents) == count($is_verified))
    {
        return true;
    } else {
        return false;
    }
}

function calculate_commission($total_amount = 0,$provider_commission = 0, $commission_type = 'percent',$type = '', $totalEarning = 0,$count=0){
    if($total_amount === 0){
        return [
            'value' => '-',
            'number_format' => 0
          ];
    }
    switch ($type) {
      case 'provider':
          $earning =   ($total_amount) - ($provider_commission * $count);
          if($commission_type === 'percent'){
              $earning =  ($total_amount) * $provider_commission / 100;
          }
          $final_amount = $earning - $totalEarning;

          if(abs($final_amount) < 1) { // treat values less than 0.0001 as 0
             $final_amount = 0;
           }


          break;
      default:
          $earning =  $provider_commission * $count ;
          if($commission_type === 'percent'){
              $earning = ($total_amount) * (100 - $provider_commission) / 100;
          }
          $final_amount = $earning;
          break;
    }
    return [
        'value' => getPriceFormat($final_amount),
        'number_format' => $final_amount
      ];
}

function get_provider_commission($bookings) {
      $all_booking_total = $bookings->map(function ($booking) {
          return $booking->total_amount;
      })->toArray();

      $all_booking_tax = $bookings->map(function ($booking) {
          return $booking->getTaxesValue();
      })->toArray();

      $total = array_reduce($all_booking_total, function ($value1, $value2) {
          return $value1 + $value2;
      }, 0);

      $tax = array_reduce($all_booking_tax, function ($tax1, $tax2) {
          return $tax1 + $tax2;
      }, 0);

      $total_amount = $total;

      return [
          'total_amount' => $total_amount,
          'tax' => $tax,
          'total' => $total,
          'all_booking_tax' => $all_booking_tax,
          'all_booking_total' => $all_booking_total,
      ];
}

function get_handyman_provider_commission($handyman_id){
    $hadnymantype_id = !empty($handyman_id) ?$handyman_id : 1;
    $get_commission = \App\Models\HandymanType::withTrashed()->where('id',$hadnymantype_id)->first();
    if($get_commission){
        $commission_value = $get_commission->commission;
        $commission_type = $get_commission->type;

        $commission = getPriceFormat($commission_value);
        if($commission_type === 'percent'){
            $commission = $commission_value . '%';
        }

        return $commission;
    }
    return '-';
}

function adminEarning(){
    $revenuedata= \App\Models\Payment::selectRaw('sum(total_amount) as total , booking_id, DATE_FORMAT(datetime , "%m") as month' )
    ->whereYear('datetime',date('Y'))
    ->where('payment_status','paid')
    ->groupBy('month');
    $revenuedata = $revenuedata->get()->toArray();
    foreach ($revenuedata as $key => $value) {
       $total_amount = $value['total'];
       $booking  =  \App\Models\Booking::where('id',$value['booking_id'])->first();
       if(!empty($booking)){
        $provider = App\Models\User::where('id',$booking->provider_id)->first();
        $provider_commission = optional($provider->providertype)->commission;
        $provider_type = optional($provider->providertype)->type;
        $earning =   ($total_amount) - ($provider_commission );
        if($provider_type === 'percent'){
            $earning =  ($total_amount) * $provider_commission / 100;
        }
        $revenuedata[$key]['providerEarning'] = $earning;
        $revenuedata[$key]['afterAmount'] = $total_amount - $earning;

        }else{
            $revenuedata[$key]['providerEarning'] = 0;
            $revenuedata[$key]['afterAmount'] = 0;
        }
    }

    $data['revenueData']    =    [];
    for($i = 1; $i <= 12; $i++ ){
        $revenueData = 0;
        foreach($revenuedata as $revenue){
            if((int)$revenue['month'] == $i){
                $data['revenueData'][] = (int)$revenue['afterAmount'];
                $revenueData++;
            }
        }
        if($revenueData == 0){
            $data['revenueData'][] = 0 ;
        }
    }
    return $data['revenueData'];
}

function getTimeZone(){
    $timezone = \App\Models\AppSetting::first();
    return $timezone->time_zone ?? 'UTC';
}

function get_plan_expiration_date($plan_start_date = '',$plan_type = '',$left_days = 0, $plan_duration = 1){
    $start_at = new \Carbon\Carbon( $plan_start_date);
    $end_date = '';

    if($plan_type === 'weekly'){
       $getdays = App\Models\Plans::where('identifier','free')->first();
       $getdays = $getdays->trial_period;
       $days = $left_days + $getdays;
       $end_date =  $start_at->addDays($days);
    }
    if($plan_type === 'monthly'){
        $end_date =  $start_at->addMonths($plan_duration)->addDays($left_days);
    }
    if($plan_type === 'yearly'){
        $end_date =  $start_at->addYears($plan_duration)->addDays($left_days);
    }
    return $end_date->format('Y-m-d H:i:s');
}

function get_user_active_plan($user_id){
    $get_provider_plan  =  \App\Models\ProviderSubscription::where('user_id',$user_id)->where('status',config('constant.SUBSCRIPTION_STATUS.ACTIVE'))->first();
    $activeplan = null;
    if(!empty($get_provider_plan)){
        $activeplan = new App\Http\Resources\API\ProviderSubscribeResource($get_provider_plan);
    }
    return $activeplan;
}

function is_subscribed_user($user_id){
    $user_subscribed = \App\Models\ProviderSubscription::where('user_id',$user_id)->where('status',config('constant.SUBSCRIPTION_STATUS.ACTIVE'))->first();
    $value = 0;
    if($user_subscribed){
        $value = 1;
    }
    return $value;
}

function check_days_left_plan($old_plan,$new_plan){
    $previous_plan_start = $old_plan->start_at;
    $previous_plan_end = new \Carbon\Carbon($old_plan->end_at);
    $new_plan_start = new \Carbon\Carbon(date('Y-m-d H:i:s'));
    $left_days = $previous_plan_end->diffInDays($new_plan_start);
    return $left_days;
}

function user_last_plan($user_id){
    $user_subscribed = \App\Models\ProviderSubscription::where('user_id',$user_id)
                    ->where('status',config('constant.SUBSCRIPTION_STATUS.INACTIVE'))->orderBy('id','desc')->first();
    $inactivePlan = null;
    if(!empty($user_subscribed)){
        $inactivePlan = new App\Http\Resources\API\ProviderSubscribeResource($user_subscribed);
    }
    return $inactivePlan;
}

function is_any_plan_active($user_id){
    $user_subscribed = \App\Models\ProviderSubscription::where('user_id',$user_id)->where('status',config('constant.SUBSCRIPTION_STATUS.ACTIVE'))->first();
    $value = 0;
    if($user_subscribed){
        $value = 1;
    }
    return $value;
}

function default_earning_type(){
    $gettype = \App\Models\Setting::where('type','earning-setting')->where('key','earning-setting')->first();
    if ($gettype !== null) {
        $earningtype = $gettype->value ?? 'commission';
    } else {
        $earningtype = 'commission';
    }
    return $earningtype;
}

function get_provider_plan_limit($provider_id,$type){
    $limit_array = array();

    if(is_any_plan_active($provider_id) == 1){
        $exceed = '';
        $get_current_plan = get_user_active_plan($provider_id);
        if($get_current_plan->plan_type === 'limited'){
            $get_plan_limit = json_decode($get_current_plan->plan_limitation,true);
            $plan_start_date =  date('Y-m-d',strtotime( $get_current_plan->start_at));

            if($type === 'service'){
                $limit_array = $get_plan_limit['service'];
                $provider_service_count = \App\Models\Service::where('provider_id',$provider_id)->whereDate('created_at', '>=', $plan_start_date)->count();
                if($limit_array['is_checked'] == 'on' && $limit_array['limit'] != null){
                    if( $provider_service_count >= $limit_array['limit']){
                      $exceed = 1; // 1 for exceed limit;
                    }
                }elseif ($limit_array['is_checked'] === 'on' && $limit_array['limit'] == null) {
                     $exceed = 0;
                }
            }
            if($type === 'featured_service'){
                $limit_array = $get_plan_limit['featured_service'];
                $provider_featured_service_count = \App\Models\Service::where('provider_id',$provider_id)->where('is_featured',1)->whereDate('created_at', '>=', $plan_start_date)->count();
                if($limit_array['is_checked'] == 'on' && $limit_array['limit'] != null){
                    if($provider_featured_service_count >= $limit_array['limit']){
                      $exceed = 1; // 1 for exceed limit;
                    }
                }elseif ($limit_array['is_checked'] === 'on' && $limit_array['limit'] == null) {
                     $exceed = 0;
                }
            }
            if($type === 'handyman'){
                $limit_array = $get_plan_limit['handyman'];
                $handyman_count = \App\Models\User::where('provider_id',$provider_id)->whereDate('created_at', '>=', $plan_start_date)->count();
                if($limit_array['is_checked'] == 'on' && $limit_array['limit'] != null){
                    if($handyman_count >= $limit_array['limit']){
                      $exceed = 1; // 1 for exceed limit;
                    }
                }elseif ($limit_array['is_checked'] === 'on' && $limit_array['limit'] == null) {
                     $exceed = 0;
                }
            }

        }else{
            return;
        }
    }else{
        return;
    }
    return $exceed;
}

function sendNotification($type,$user,$data){

    $othersetting = \App\Models\Setting::where('type','OTHER_SETTING')->first();

    $decodedata = $othersetting ? json_decode($othersetting['value']) : null;
    $firebase_notification = $decodedata->firebase_notification;

    if($firebase_notification == 1){

     $apiKey= isset($decodedata->firebase_key) ? $decodedata->firebase_key : null;

     $apiUrl = 'https://fcm.googleapis.com/fcm/send';
     $apiKey =$apiKey;

     $headers = [
         'Authorization: key=' . $apiKey,
         'Content-Type: application/json',
     ];

     $heading   ='#'.$data['id'].' '.str_replace("_"," ",ucfirst($data['subject']));
     $content   = $data['message'];


     $firebase_data = [
         'to'=>'/topics/user_'.$user->id,
         'collapse_key' => 'type_a',
         'notification' => [
             'body' =>   $content,
             'title' => $heading ,
         ],
         'data' => [
            'type' => $data['type'],
            'id' => $data['id']
         ],
     ];

     $ch = curl_init($apiUrl);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($firebase_data));

     $response = curl_exec($ch);

     curl_close($ch);

    }

      $childData = array(
      "id"=> $data['id'],
       "type"=>$data['type'],
       "subject"=>$data['subject'],
       "message"=>$data['message'],
       'notification-type' => $data['notification-type']
      );

    $notification = \App\Models\Notification::create(
        array(
            'id' => Illuminate\Support\Str::random(32),
            'type' => $data['type'],
            'notifiable_type'=> 'App\Models\User',
            'notifiable_id'=>$user->id,
            'data'=>json_encode($childData)
        )
    );


}

function getServiceTimeSlot($provider_id){
    $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
    $admin = json_decode($sitesetup->value);
  date_default_timezone_set($admin->time_zone ?? 'UTC');

    $current_time = \Carbon\Carbon::now();
    $time = $current_time->toTimeString();
    $current_day = strtolower(date('D'));

    $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    $handyman_count = \App\Models\User::where('provider_id', $provider_id)->where('is_available', 1)->count() + 1;

    $providerSlots = \App\Models\ProviderSlotMapping::where('provider_id', $provider_id)
        ->whereIn('days', $days)
        ->orderBy('start_at', 'asc')
        ->get();

    $bookings = \App\Models\Booking::where('provider_id', $provider_id)->get();
    $booking_count = count($bookings);

    $slotsArray = [];

    foreach ($days as $value) {
        $slot = $providerSlots->where('days', $value);

        if ($current_day === $value) {
            $slot = $slot->where('start_at', '>', $time);
        }

        $filteredSlots = $slot->pluck('start_at')->toArray();

        if ($handyman_count == $booking_count) {
            $filteredSlots = array_diff($filteredSlots, $bookings->pluck('start_at')->toArray());
        }

        $obj = [
            "day" => $value,
            "slot" => $filteredSlots,
        ];

        array_push($slotsArray, $obj);
    }

    return $slotsArray;
}

function bookingstatus($status){
    switch ($status) {
        case 'Pending':
            $html = '<span class="badge badge-warning ">'.$status.'</span>';

            break;

        case 'Accepted':
            $html = '<span class="badge badge-primary">'.$status.'</span>';

            break;


        case 'Ongoing':
            $html = '<span class="badge badge-warning">'.$status.'</span>';

            break;

        case 'In Progress':
            $html = '<span class="badge badge-info">'.$status.'</span>';

            break;

        case 'Hold':
            $html = '<span class="badge badge-dark text-white">'.$status.'</span>';

            break;

        case 'Cancelled':
            $html = '<span class="badge badge-light">'.$status.'</span>';

            break;

        case 'Rejected':
            $html = '<span class="badge badge-light">'.$status.'</span>';

            break;

        case 'Completed':
            $html = '<span class="badge badge-success">'.$status.'</span>';

            break;

        default:
            $html = '<span class="badge badge-danger">'.$status.'</span>';
            break;
    }
   return $html;
}

function today_cash_total($user_id,$to='',$from='',$type = ''){
    $amount = 0;

    if (auth()->user()->hasAnyRole(['handyman'])) {
        $amount = \App\Models\PaymentHistory::where('receiver_id', $user_id)
        ->where('action', 'handyman_approved_cash')
        ->where(function ($query) use ($from, $to) {
            $query->where('status', 'approved_by_handyman')
                ->orWhere('status', 'send_to_provider');
        })
        ->whereDate('datetime', '>=', $from)
        ->whereDate('datetime', '<=', $to)
        ->sum('total_amount');

    }

    if (auth()->user()->hasAnyRole(['provider'])) {
        $amount = \App\Models\PaymentHistory::where('receiver_id', $user_id)
        ->where('action', 'handyman_send_provider')
        ->where(function ($query) use ($from, $to) {
            $query->where('status', 'pending_by_admin')
                ->orWhere('status', 'approved_by_provider');
        })
        ->whereDate('datetime', '>=', $from)
        ->whereDate('datetime', '<=', $to)
        ->sum('total_amount');
    }
    return $amount;
}

function total_cash($user_id){
    $amount = 0;


    if (auth()->user()->hasAnyRole(['handyman'])) {

        $amount = \App\Models\PaymentHistory::where('receiver_id', $user_id)
        ->where(function ($query) {
            $query->where('action', 'handyman_approved_cash')
                ->where('status', 'approved_by_handyman')
                ->orWhere('status', 'send_to_provider');
        })
        ->sum('total_amount');

    }
    if (auth()->user()->hasAnyRole(['provider'])) {

        $amount = \App\Models\PaymentHistory::where('receiver_id', $user_id)
        ->where(function ($query) {
            $query->where('action', 'handyman_send_provider')
                ->where('status', 'approved_by_provider')
                ->orWhere('status', 'pending_by_admin');
        })
        ->sum('total_amount');

    }


    return $amount;
}

function admin_id(){
    $user = \App\Models\User::getUserByKeyValue('user_type','admin');
    return $user->id;
}

function get_user_name($user_id){
    $name = '';
    $user = \App\Models\User::getUserByKeyValue( 'id', $user_id );
    if($user !== null){
        $name = $user->display_name;
    }
    return $name;
}

function set_admin_approved_cash($payment_id){
    $payment_status_check =  \App\Models\PaymentHistory::where('payment_id',$payment_id)
    ->where('action','provider_send_admin')->where('status','pending_by_admin')->first();
    if($payment_status_check !== null){
        $status = '<a class="btn-sm text-white btn-success"  href='.route('cash.approve',$payment_id).'><i class="fa fa-check"></i>Approve</a>';
    }else{
        $status = '-';
    }
    return $status;
}

function last_status($payment_id){
    $payment_status_check =  \App\Models\PaymentHistory::orderBy('id','desc')->where('payment_id',$payment_id)->first();
    if($payment_status_check !== null){
        $status = '<span class="text-center badge badge-primary1">'.str_replace('_'," ",ucfirst($payment_status_check->status)).'</span>';
    }else{
        $status = '<span class="text-center d-block">-</span>';
    }
    return $status;
}

function providerpayout_rezopayX($data){

    $rezorpay_data = \App\Models\PaymentGateway::where('type','razorPayX')->first();


    if($rezorpay_data){

            $is_test=$rezorpay_data['is_test'];

            if($is_test==1){

            $json_data=$rezorpay_data['value'];

            }else{

            $json_data=$rezorpay_data['live_value'];

            }

        $sitesetup = \App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;

        $currency_country_id = $sitesetupdata['default_currency'] ?? null;


        $country_data=\App\Models\Country::where('id',$currency_country_id)->first();

        $currency=$country_data['currency_code'];

        $razopayX_credentials = json_decode($json_data, true);

        $url=$razopayX_credentials['razorx_url'];
        $key=$razopayX_credentials['razorx_key'];
        $secret=$razopayX_credentials['razorx_secret'];
        $RazorpayXaccount=$razopayX_credentials['razorx_account'];


        $provider_id=$data['provider_id'];
        $payout_amount=$data['amount'];
        $bank_id=$data['bank'];

        $providers_details=\App\Models\User::where('id',$provider_id)->first();

            $email=$providers_details['email'];
            $first_name=$providers_details['first_name'];
            $last_name=$providers_details['last_name'];
            $contact_number=$providers_details['contact_number'];
            $user_type=$providers_details['user_type'];

            $bank_details=\App\Models\Bank::where('id',$bank_id)->first();

                $bank_name=$bank_details['bank_name'];
                $account_number=$bank_details['account_no'];
                $ifsc=$bank_details['ifsc_no'];

                $payout_data = array(
                    "account_number" =>$RazorpayXaccount,
                    "amount" =>$payout_amount*100,
                    "currency" => $currency,
                    "mode" => "NEFT",
                    "purpose" => "payout",
                    "fund_account" => array(
                                    "account_type" => "bank_account",
                                        "bank_account" => array(
                                            "name" => $first_name.$last_name ,
                                            "ifsc" =>$ifsc ,
                                            "account_number" => $account_number
                                        ),
                    "contact" => array(
                                    "name" => $first_name.$last_name,
                                    "email" =>  $email,
                                    "contact" => $contact_number,
                                    "type" => "vendor",
                                )
                            ),
                    "queue_if_low_balance" => true,

                );

            // Convert data to JSON
            $json_data = json_encode($payout_data);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic '. base64_encode($key . ':' . $secret)
            ));

            $response = curl_exec($ch);

            return $response;
    }else{

        return $response='';
   }

}

function providerpayout_stripe($data){

    //Stripe Payment

    $stripe_data = \App\Models\PaymentGateway::where('type','stripe')->first();

    if($stripe_data){

        $is_test=$stripe_data['is_test'];

        if($is_test==1){

          $json_data=$stripe_data['value'];

        }else{

           $json_data=$stripe_data['live_value'];

        }

         $stripe_credentials = json_decode($json_data, true);

         $secret_key=$stripe_credentials['stripe_key'];

        $sitesetup = \App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;

        $currency_country_id = $sitesetupdata['default_currency'] ?? null;

          $country_data=\App\Models\Country::where('id',$currency_country_id)->first();

          $country=$country_data['code'];

          $currency=strtolower($country_data['currency_code']);


          $provider_id=$data['provider_id'];
          $payout_amount=$data['amount'];
          $bank_id=$data['bank'];

          $bank_details=\App\Models\Bank::where('id',$bank_id)->first();

          $bank_name=$bank_details['bank_name'];
          $account_number=$bank_details['account_no'];
          $ifsc=$bank_details['ifsc_no'];
          $stripe_account=$bank_details['stripe_account'];

          if($stripe_account ==''){

            $providers_details=\App\Models\User::where('id',$provider_id)->first();
            $email=$providers_details['email'];
            $first_name=$providers_details['first_name'];
            $last_name=$providers_details['last_name'];
            $contact_number=$providers_details['contact_number'];
            $user_type=$providers_details['user_type'];

            $current_datetime=time();

            $ip_address=file_get_contents('https://api.ipify.org');

          try{

            $stripe = new \Stripe\StripeClient($secret_key);

             $stripedata=$stripe->accounts->create(
               [
                 'country' => $country,
                 'type' => 'custom',
                 'bank_account' => [
                     'account_number' => $account_number,
                     'country' => $country,
                     'account_holder_name' => $first_name.$last_name,
                     'routing_number' => $ifsc
                 ],

                 'capabilities' => [
                     'transfers' => [
                         'requested' => true
                     ]
                 ],
                 'business_type' => 'individual',
                 'country' => $country,
                 'email' => $email,
                 'individual' => [
                     'first_name' => $first_name,
                     'last_name' => $last_name
                 ],
                 'business_profile' => [
                    'name' => $first_name.$last_name,
                    'url' => 'demo.com'
                ],
                 'tos_acceptance' => [
                     'date' =>$current_datetime,
                     'ip' => $ip_address
                 ]
               ]
             );

             $stripe_account= $stripedata['id'];

            \App\Models\Bank::where('id',$bank_id)->update(['stripe_account'=>$stripe_account]);

            }catch(Stripe\Exception\ApiErrorException $e){

                   $error= $e->getError();

                   if($error ==''){

                    return $response='';

                    }else{

                      $error['status']=400;

                      return $error;

                  }

              }

           }

           $data=[

             'secret_key'=>$secret_key,
             'amount'=>$payout_amount,
             'currency'=>$currency,
             'stripe_account'=>$stripe_account
           ];



       $bank_transfer=create_stripe_transfer($data);

       return $bank_transfer;


     }else{

        return $response='';
    }

}

function create_stripe_transfer($data){
        try{


          \Stripe\Stripe::setApiKey($data['secret_key']);

          $transfer = \Stripe\Transfer::create([
            "amount" => $data['amount']*100,
            "currency" =>  $data['currency'],
            "destination" =>$data['stripe_account'],
          ]);

         $payout=create_bank_tranfer($data);

         return $payout;


        }catch(Stripe\Exception\ApiErrorException $e){


          $error= $e->getError();

           $error['status']=400;

           if($error ==''){

            return $response='';

            }else{

             $error['status']=400;
             return $error;

          }

        }

}

function create_bank_tranfer($data){

    try{

        \Stripe\Stripe::setApiKey($data['secret_key']);

        $payout = \Stripe\Payout::create([
          'amount' =>$data['amount']*100,
          'currency' => $data['currency'],
           ], [
          'stripe_account' => $data['stripe_account'],

         ]);

         return $payout;

        }catch(Stripe\Exception\ApiErrorException $e){


           $error= $e->getError();


             if($error ==''){

              return $response='';

              }else{

               $error['status']=400;
               return $error;

            }

          }


}

function calculateReadingTime($content, $wpm = 100) {
    $wordCount = str_word_count(strip_tags($content));

    $readingTime = intval($wordCount / $wpm);

    return $readingTime;
}

function formatCurrency($number, $noOfDecimal, $currencyPosition, $currencySymbol) {

    $formattedNumber = number_format($number, $noOfDecimal, '.', '');
    $parts = explode('.', $formattedNumber);
    $integerPart = $parts[0];
    $decimalPart = isset($parts[1]) ? $parts[1] : '';

    $currencyString = '';

    if ($currencyPosition == 'left') {
        $currencyString .= $currencySymbol;

        $currencyString .= $integerPart;

        if ($noOfDecimal > 0) {
            $currencyString .= '.' . $decimalPart;
        }
    }

    if ($currencyPosition == 'right' ) {

        if ($noOfDecimal > 0) {
            $currencyString .= $integerPart . '.' . $decimalPart;
        }

        $currencyString .= $currencySymbol;
    }

    return $currencyString;
}

function  getPaymentMethodkey($type){

    $pyament_gateway=App\Models\PaymentGateway::query();

    $payment_geteway_value=null;

    switch($type) {

        case 'stripe':

          $pyament_gateway_data = $pyament_gateway->where('type',$type)->first();

          if($pyament_gateway_data){

            if($pyament_gateway_data->is_test ==1){

                $payment_geteway_value=json_decode($pyament_gateway_data->value,true);


               }else{

                   $payment_geteway_value=json_decode($pyament_gateway_data->live_value,true);

               }

          }

            break;

        }

        return $payment_geteway_value;

}

function getstripepayments($data){
    $baseURL = env('APP_URL');

    $stripe_key_data = getPaymentMethodkey($data['payment_type']);

    $stripe_secret = $stripe_key_data['stripe_key'];

    $booking=App\Models\Booking::where('id',$data['booking_id'])->with('service')->first();

    try {
        $stripe = new \Stripe\StripeClient($stripe_secret);
        $checkout_session = $stripe->checkout->sessions->create([

            'success_url' => $baseURL.'/save-stripe-payment/'.$data['booking_id'].'?type='.$data['type'],
            'payment_method_types' => ['card'],
            'billing_address_collection' => 'required',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $data['currency_code'],
                        'product_data' => [
                            'name' => $booking->service->name,
                        ],
                        'unit_amount' => $data['total_amount'] * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
        ]);
    } catch (\Exception $e) {
        $message = $e->getMessage();

        $checkout_session = [
            'message' => $message,
            'status' => false,
        ];
    }

  return $checkout_session;
}

function getstripePaymnetId($stripe_session_id,$payment_type){
    $stripe_key_data = getPaymentMethodkey($payment_type);

    $stripe_secret = $stripe_key_data['stripe_key'];

    $stripe = new \Stripe\StripeClient($stripe_secret);
    $session_object = $stripe->checkout->sessions->retrieve($stripe_session_id, []);

    return $session_object;
}

function default_user_name(){
    return __('messages.unknown_user');
}

function onesingle($fields){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization:Basic '.env('ONESIGNAL_REST_API_KEY'),
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        Log::error('OneSignal API Error: ' . $response);
    }
    curl_close($ch);
}

function addWalletAmount($data){

    $baseURL = env('APP_URL');

    // Retrieve the Stripe secret key
    $stripe_key_data = getPaymentMethodkey($data['payment_type']);
    $stripe_secret = $stripe_key_data['stripe_key'];

    // Retrieve wallet details
    $wallet = App\Models\Wallet::where('user_id', $data['customer_id'])->first();

    try {
        // Create the Stripe checkout session
        $stripe = new \Stripe\StripeClient($stripe_secret);
        $checkout_session = $stripe->checkout->sessions->create([
            'success_url' => $baseURL.'/save-wallet-stripe-payment/'.$data['customer_id'].'?amount='.$data['amount'], // Use the route name
            'payment_method_types' => ['card'],
            'billing_address_collection' => 'required',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $data['currency_code'],
                        'unit_amount' => $data['amount'] * 100, // Amount in cents
                        'product_data' => [
                            'name' => 'Wallet Top-Up', // Change this if needed
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
        ]);
    } catch (\Exception $e) {
        // Handle exceptions
        $message = $e->getMessage();
        $checkout_session = [
            'message' => $message,
            'status' => false,
        ];
    }

    return $checkout_session;
}

function fcm($fields)
{
    $otherSetting = \App\Models\Setting::where('type', 'OTHER_SETTING')->first();
    $other = json_decode($otherSetting->value) ?? null;
    $firebaseKey = $other->firebase_key;
    $headers = [
        'Authorization: key=' .$firebaseKey,
        'Content-Type: application/json',
    ];

    $ch = curl_init('https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $response = curl_exec($ch);

    curl_close($ch);
}
