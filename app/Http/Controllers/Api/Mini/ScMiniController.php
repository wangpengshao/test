<?php

namespace App\Http\Controllers\Api\Mini;

use App\Api\Helpers\ApiResponse;
use App\Services\XmlService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScMiniController extends Controller
{
    use  ApiResponse;

    private $key = 'SC_LIBRARY';

    public function __construct(Request $request)
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->middleware('checkCefMiniToken');
        $this->token = $request->input('token');
    }

    public function readerCertification(Request $request)
    {
        if (!$request->filled(['card', 'password'])) {
            return $this->failed('lack of parameter');
        }

        $rdid = $request->input('card');
        $password = $request->input('password');

        $params = http_build_query([
            'op' => 'bor-auth',
            'bor-id' => $rdid,
            'verification' => $password,
            'user_name' => 'WWW-RR',
            'user_password' => 'ALEPH'
        ]);
        $url = 'http://118.112.186.160:8991/X?' . $params;

        $http = new Client();
        $response = $http ->get($url);
        $response = simplexml_load_string((string)$response->getBody());

        return $this->success($response);

    }

    public function checkRdid(Request $request)
    {
        if (!$request->filled(['rdid', 'sign'])) {
            return $this->failed('lack of parameter');
        }
        $rdid = $request->input('rdid');
        $sign = $request->input('sign');

        if($sign != md5($rdid.$this->key)){
            return $this->failed('Signature Error');
        }

        $url = 'http://118.112.186.160:8991/X?op=bor_info&bor_id=' . $rdid;

        $http = new Client();
        $response = $http ->get($url);
        $response = simplexml_load_string((string)$response->getBody());

        if($response->error){
            return $this->failed('无效的读者证号');
        }

        return $this->success($response);
    }

    public function save(Request $request)
    {
        if (!$request->filled(['name', 'gender', 'IdCard', 'mobile', 'address', 'education', 'specialty', 'positions', 'password', 'uid', 'sign'])) {
            return $this->failed('lack of parameter');
        }

        $sign = $request->input('sign');
        $name = $request->input('name');
        $gender = $request->input('gender');
        $IdCard = $request->input('IdCard');
        $telephone = $request->input('telephone');
        $mobile = $request->input('mobile');
        $address = $request->input('address');
        $zipCode = $request->input('zipCode');
        $email = $request->input('email');
        $education = $request->input('education');
        $specialty = $request->input('specialty');
        $positions = $request->input('positions');
        $password = $request->input('password');
        $uid = $request->input('uid');

        $arguments = [
            'name' => $name,
            'gender' => $gender,
            'IdCard' => $IdCard,
            'telephone' => $telephone ? $telephone : '',
            'mobile' => $mobile,
            'address' => $address,
            'zipCode' => $zipCode ? $zipCode : '',
            'email' => $email ? $email : '',
            'education' => $education,
            'specialty' => $specialty,
            'positions' => $positions,
            'password' => $password,
            'uid' => $uid
        ];
        ksort($arguments);
        if($sign != md5(urldecode(http_build_query($arguments).$this->key))){
            return $this->failed('Signature Error');
        }

        $birthday = substr($IdCard, 6,8);
        $xml_full_req = $this->dataTpl($name, $gender, $IdCard, $birthday, $telephone, $mobile, $address, $zipCode, $email, $education, $specialty, $positions, Str::upper($password));
        $params = [
            'op' => 'update_bor',
            'library' => 'SCL50',
            'update_flag' => 'Y',
            'xml_full_req' => (string)$xml_full_req,
            'user_name' => 'WWW-RR',
            'user_password' => 'ALEPH'
        ];
        $url = 'http://118.112.186.160:8991/X';
        $http = new Client();
        $response = $http ->request('POST', $url, ['form_params' => $params]);
        $response = simplexml_load_string((string)$response->getBody());

        if(strpos((string)$response->error, 'Succeeded') !== false){

//            $where = [
//                'token' => $this->token,
//                'uid' => $request->input('uid'),
//                'card' => $IdCard
//            ];
//            $exists = DB::table('mini_registration_u')->where($where)->exists();
//            if (!$exists) {
//                $where['created_at'] = date('Y-m-d H:i:s');
//                DB::table('mini_registration_u')->insert($where);
//            }

            return $this->message('办理成功!',true);
        }
        if(strpos((string)$response->error, 'Cannot update/delete Z308 record. Login record belongs to another user.') !== false){
            return $this->failed('该身份证号已办理过读者证，请勿重复办理!');
        }
        return $this->failed((string)$response->error);

    }

    /**
     * @param $name 姓名
     * @param $gender 性别
     * @param $IdCard 身份证
     * @param $birthday 生日
     * @param $telephone 电话，可以是固定电话
     * @param $mobile 手机号
     * @param $address 地址
     * @param $zipCode 邮编
     * @param $email 邮箱
     * @param $education 学历
     * @param $specialty 专业
     * @param $positions  职位
     * @param $password  密码
     * @param $library
     * @return string
     */
    protected function dataTpl($name, $gender, $IdCard, $birthday, $telephone, $mobile, $address, $zipCode, $email, $education, $specialty, $positions, $password, $library='SCL50')
    //protected function dataTpl($data, $library='SCL50')
    {
        $title = $gender == 'M' ? 'Mr' : 'Ms';
        $addrArr = $this->splitAddr($address);

        $xml = '<?xml version="1.0"?>
                <p-file-20>
                    <patron-record>
                        <z303>
                            <match-id-type>00</match-id-type>
                            <match-id>NULL</match-id>
                            <record-action>A</record-action>
                            <z303-id>NULL</z303-id>
                            <z303-proxy-for-id></z303-proxy-for-id>
                            <z303-primary-id></z303-primary-id>
                            <z303-name-key></z303-name-key>
                            <z303-user-type></z303-user-type>
                            <z303-user-library>'. $library .'</z303-user-library>
                            <z303-open-date>'. date('Ymd'). '</z303-open-date>
                            <z303-update-date>'. date('Ymd') .'</z303-update-date>
                            <z303-con-lng>CHI</z303-con-lng>
                            <z303-alpha>L</z303-alpha>
                            <z303-name>'. $name .'</z303-name>
                            <z303-title>'. $title .'</z303-title>
                            <z303-delinq-1>00</z303-delinq-1>
                            <z303-delinq-n-1></z303-delinq-n-1>
                            <z303-delinq-1-update-date>00000000</z303-delinq-1-update-date>
                            <z303-delinq-1-cat-name></z303-delinq-1-cat-name>
                            <z303-delinq-2>00</z303-delinq-2>
                            <z303-delinq-n-2></z303-delinq-n-2>
                            <z303-delinq-2-update-date>00000000</z303-delinq-2-update-date>
                            <z303-delinq-2-cat-name></z303-delinq-2-cat-name>
                            <z303-delinq-3>00</z303-delinq-3>
                            <z303-delinq-n-3></z303-delinq-n-3>
                            <z303-delinq-3-update-date>00000000</z303-delinq-3-update-date>
                            <z303-delinq-3-cat-name></z303-delinq-3-cat-name>
                            <z303-budget></z303-budget>
                            <z303-profile-id></z303-profile-id>
                            <z303-ill-library></z303-ill-library>
                            <z303-home-library>'. $library .'</z303-home-library>
                            <z303-field-1>'. $education .'</z303-field-1>
                            <z303-field-2>'. $specialty .'</z303-field-2>
                            <z303-field-3>'. $positions .'</z303-field-3>
                            <z303-note-1></z303-note-1>
                            <z303-note-2>'. $IdCard .'</z303-note-2>
                            <z303-salutation></z303-salutation>
                            <z303-ill-total-limit>9999</z303-ill-total-limit>
                            <z303-ill-active-limit>0100</z303-ill-active-limit>
                            <z303-dispatch-library></z303-dispatch-library>
                            <z303-birth-date>'. $birthday . '</z303-birth-date>
                            <z303-export-consent>Y</z303-export-consent>
                            <z303-proxy-id-type>00</z303-proxy-id-type>
                            <z303-send-all-letters>Y</z303-send-all-letters>
                            <z303-plain-html></z303-plain-html>
                            <z303-want-sms>Y</z303-want-sms>
                            <z303-plif-modification></z303-plif-modification>
                            <z303-title-req-limit>0000</z303-title-req-limit>
                            <z303-gender>'. $gender .'</z303-gender>
                            <z303-birthplace></z303-birthplace>
                        </z303>
                        <z304>
                            <record-action>A</record-action>
                            <email-address>'. $email .'</email-address>
                            <z304-id></z304-id>
                            <z304-sequence>01</z304-sequence>
                            <z304-address-0>'. $name .'</z304-address-0>
                            <z304-address-1>'. $addrArr[0] .'</z304-address-1>
                            <z304-address-2>'. $addrArr[1] .'</z304-address-2>
                            <z304-address-3>'. $addrArr[2] .'</z304-address-3>
                            <z304-address-4>'. $addrArr[3] .'</z304-address-4>
                            <z304-zip>'. $zipCode .'</z304-zip>
                            <z304-email-address>'. $email .'</z304-email-address>
                            <z304-telephone>'. $telephone .'</z304-telephone>
                            <z304-date-from>'. date('Ymd'). '</z304-date-from>
                            <z304-date-to>20991231</z304-date-to>
                            <z304-address-type>01</z304-address-type>
                            <z304-telephone-2></z304-telephone-2>
                            <z304-telephone-3></z304-telephone-3>
                            <z304-telephone-4></z304-telephone-4>
                            <z304-sms-number>'. $mobile .'</z304-sms-number>
                            <z304-update-date></z304-update-date>
                            <z304-cat-name>WWW-RR</z304-cat-name>
                        </z304>
                        <z305>
                            <record-action>A</record-action>
                            <z305-id></z305-id>
                            <z305-sub-library>'. $library .'</z305-sub-library>
                            <z305-open-date>'. date('Ymd'). '</z305-open-date>
                            <z305-update-date>'. date('Ymd'). '</z305-update-date>
                            <z305-bor-type></z305-bor-type>
                            <z305-bor-status>30</z305-bor-status>
                            <z305-registration-date>'. date('Ymd'). '</z305-registration-date>
                            <z305-expiry-date>'. date('Ymd', strtotime('+ 10 year')) .'</z305-expiry-date>
                            <z305-note></z305-note>
                            <z305-loan-permission>Y</z305-loan-permission>
                            <z305-photo-permission>Y</z305-photo-permission>
                            <z305-over-permission>N</z305-over-permission>
                            <z305-multi-hold>N</z305-multi-hold>
                            <z305-loan-check>Y</z305-loan-check>
                            <z305-hold-permission>Y</z305-hold-permission>
                            <z305-renew-permission>Y</z305-renew-permission>
                            <z305-rr-permission>Y</z305-rr-permission>
                            <z305-ignore-late-return>N</z305-ignore-late-return>
                            <z305-last-activity-date></z305-last-activity-date>
                            <z305-photo-charge>C</z305-photo-charge>
                            <z305-no-loan>0000</z305-no-loan>
                            <z305-no-hold>0000</z305-no-hold>
                            <z305-no-photo>0000</z305-no-photo>
                            <z305-no-cash>0000</z305-no-cash>
                            <z305-cash-limit>0000000500</z305-cash-limit>
                            <z305-credit-debit></z305-credit-debit>
                            <z305-sum>0.00</z305-sum>
                            <z305-delinq-1>00</z305-delinq-1>
                            <z305-delinq-n-1></z305-delinq-n-1>
                            <z305-delinq-1-update-date>00000000</z305-delinq-1-update-date>
                            <z305-delinq-1-cat-name></z305-delinq-1-cat-name>
                            <z305-delinq-2>00</z305-delinq-2>
                            <z305-delinq-n-2></z305-delinq-n-2>
                            <z305-delinq-2-update-date>00000000</z305-delinq-2-update-date>
                            <z305-delinq-2-cat-name></z305-delinq-2-cat-name>
                            <z305-delinq-3>00</z305-delinq-3>
                            <z305-delinq-n-3></z305-delinq-n-3>
                            <z305-delinq-3-update-date>00000000</z305-delinq-3-update-date>
                            <z305-delinq-3-cat-name></z305-delinq-3-cat-name>
                            <z305-field-1></z305-field-1>
                            <z305-field-2></z305-field-2>
                            <z305-field-3></z305-field-3>
                            <z305-hold-on-shelf>N</z305-hold-on-shelf>
                            <z305-end-block-date>00000000</z305-end-block-date>
                            <z305-booking-permission>N</z305-booking-permission>
                            <z305-booking-ignore-hours>N</z305-booking-ignore-hours>
                            <z305-rush-cat-request>N</z305-rush-cat-request>
                        </z305>
                        <z308>
                            <record-action>A</record-action>
                            <z308-key-type>01</z308-key-type>
                            <z308-key-data>'. $IdCard .'</z308-key-data>
                            <z308-user-library>'. $library .'</z308-user-library>
                            <z308-verification>'. $password .'</z308-verification>
                            <z308-verification-type>00</z308-verification-type>
                            <z308-id></z308-id>
                            <z308-status>AC</z308-status>
                            <z308-encryption>H</z308-encryption>
                        </z308>
                    </patron-record>
                </p-file-20>';

        return $xml;
    }

    protected function splitAddr($address, $length=16)
    {
        $blockData = array(0=>'', 1=>'', 2=>'', 3=>'');

        $strLength = mb_strlen($address, 'utf-8');
        $block = ceil($strLength / $length);

        for ($i=0; $i<$block; $i++){
            $blockData[$i] = mb_substr($address, $i*$length, $length,'utf-8');
        }

        return $blockData;
    }
}
