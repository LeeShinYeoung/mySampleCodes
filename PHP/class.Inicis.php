<?
/**
 * Class Inicis
 * 이니시스 결제 요청을 위한 데이터 생성 클래스
 * 
 * 필수 데이터
 * gou_number : 주문번호
 * cart_number : 카트 고유번호
 * pay_type : 결제수단 (card | bank_soodong | escrow_vbank)
 * pay_money : 결제금액
 *
 * 결제요청 데이터 생성후 반환
 * public makeInicisRequestData()
 *
 * 장바구니 데이터 반환 (구매자정보, 판매자정보)
 * public getCartData()
 *
 * 결제요청에 필요한 모든키값 반환
 * public getAllKeys()
 *
 * 결제타입 및 아이디에 따라 필요한 계정정보 반환
 * public getMidAndSignKey()
 *
 * 서명키 반환
 * public getSignatureData()
 *
 * 발생문제 누적
 * public processError($msg=null, $method=null)
 *
 * 정규화된 결제타입 반환
 * static getMethod($pay_type)
 */
class Inicis
{
    public function __construct($post_data=null, $cart_data=null)
    {
        $this->post = $post_data;
        $this->cart_data = $cart_data;
        $this->keys = array();
        $this->error = array();
        $this->error['boolean'] = false;
        $this->error['msg'] = array();
    }

    public function makeInicisRequestData()
    {
        if (empty($this->post['pay_money']))
            return $this->processError('[pay_money] is empty','makeInicisRequestData');

        $site_domain = $GLOBALS['protocol_url'].$_SERVER['HTTP_HOST'];
        $post = $this->post;
        $method = self::getMethod($post['pay_type']);
        $cart = $this->getCartData();
        $keys = $this->getAllKeys();
        if ($this->error['boolean']) return $this->error;

        ### 결제용 변수 ###
        $rtn['SUCCESS'] = 'SUCCESS'; # 성공여부
        $rtn['version'] = '1.0';
        $rtn['mid'] = $keys['mid']; # pricegolf0, pricegolf1, 재설정필요
        $rtn['goodsname'] = $cart['title']; # 상품명
        $rtn['oid'] = $post['gou_number']; # 주문번호
        $rtn['price'] = $post['pay_money']; # 결제금액
        $rtn['currency'] = 'WON';
        $rtn['buyername'] = $cart['user_name']; # 구매자명
        $rtn['buyertel'] = $cart['user_hphone']; # 구매자연락처
        $rtn['buyeremail'] = $cart['user_email']; # 구매자이메일
        $rtn['returnUrl'] = "{$site_domain}/pg_module/inicis/INIStdweb/INIStdPaySample/INIStdPayReturn.php"; # 리턴경로 재설정필요
        $rtn['timestamp'] = $keys['timestamp'];
        $rtn['signature'] = $keys['sign'];
        $rtn['mKey'] = $keys['mKey'];
        $rtn['gopaymethod'] = $method; # 기본옵션
        $rtn['languageView'] = "ko"; # 표시옵션
        $rtn['charset'] = 'UTF-8'; # 결과수신인코딩
        $rtn['payViewType'] = 'overlay';
        $rtn['closeUrl'] = "{$site_domain}/pg_module/inicis/INIStdweb/INIStdPaySample/close.php";
        $rtn['popupUrl'] = "{$site_domain}/pg_module/inicis/INIStdweb/INIStdPaySample/popup.php";

        #옵션추가
        switch ($method) {
            case 'VBank': # 무통장입금 추가옵션
                if (date("w") != "0")
                    $rtn['acceptmethod'] = "vbank(".date('Ymd',time()+86400).")";
                else
                    $rtn['acceptmethod'] = "vbank(".date('Ymd').")";
                if ($this->post['pay_type'] == 'escrow_vbank') # 에스크로 결제 설정
                    $rtn['acceptmethod'] .= ":useescrow";
                break;
            case 'Card': # 카드 추가옵션
            case 'card':
                $rtn['nointerest'] = ""; # 무이자할부
                $rtn['quotabase'] = "2:3:4:5:6:7:8:9:10:11:12:18:24:36"; # 할부개월
                break;
        }
        return $rtn;
    }


    /**
     * ※ 필수값
     * cart_number : 카트고유번호
     */
    public function getCartData()
    {
        if ($this->cart_data)
            return $this->cart_data;
        if (empty($this->post['cart_number']))
            return $this->processError('[cart_number] is empty','getCartData');

        $sql = "
            SELECT      A.number
                        , A.buyer_id
                        , A.seller_id
                        , A.title
                        , A.price
                        , B.user_phone
                        , B.user_hphone
                        , B.user_name
                        , B.user_email
            FROM        장바구니테이블 AS A
            LEFT OUTER JOIN 회원테이블 AS B ON A.buyer_id = B.user_id
            WHERE       A.number = {$this->post['cart_number']}
        ";
        list($this->cart_data) = util::query_to_array($sql);

        if (empty($this->cart_data))
            return $this->processError('[cart_data] is empty', 'getCartData');
        return $this->cart_data;
    }

    public function getAllKeys()
    {
        if (empty($this->keys['mid']) || empty($this->keys['signKey']))
            $this->getMidAndSignKey();
        if (empty($this->keys['sign']) || empty($this->keys['mKey']))
            $this->getSignatureData();
        if ($this->error['boolean'])
            return $this->error;

        return $this->keys;
    }

    public function getMidAndSignKey()
    {
        # 데이터 체크
        if ($this->keys['signKey'] && $this->keys['mid'])
            return array('mid'=>$this->keys['mid'], 'signKey'=>$this->keys['signKey']);
        if (empty($this->post['pay_type']))
            $this->processError('[pay_type] is empty','getMidAndSignKey');
        if (empty($this->cart_data['seller_id']))
            $this->getCartData();
        if ($this->error['boolean'])
            return $this->error;

        # mid 생성
        if ($this->post['pay_type'] == 'escrow_vbank')
            $mid = ($GLOBALS['corporate_body']) ? "IESpriceg1" : "IESpricego";
        elseif ($this->cart_data['seller_id'] == 'pricegolf')
            $mid = ($GLOBALS['corporate_body']) ? "pricegolf2" : "pricegolf0";
        else
            $mid = ($GLOBALS['corporate_body']) ? "pricegolfa" : "pricegolf1";

        # signKey 생성
        switch ($mid) {
            # corporate_body : true
            case "IESpriceg1" : $signKey = "암호_1"; break;
            case "pricegolf2" : $signKey = "암호_2"; break;
            case "pricegolfa" : $signKey = '암호_3'; break;
            # corporate_body : false
            case "IESpricego" : $signKey = '암호_4'; break;
            case "pricegolf0" : $signKey = "암호_5"; break;
            case "pricegolf1" : $signKey = "암호_6"; break;
            case "pricegoARS" : $signKey = ""; break;
            case "pricegoAR1" : $signKey = ""; break;
            default : $signKey = ""; break;
        }
        if ($signKey == "") $signKey = false;
        $this->keys['mid'] = $mid;
        $this->keys['signKey'] = $signKey;
        return array('mid'=>$this->keys['mid'], 'signKey'=>$this->keys['signKey']);
    }

    public function getSignatureData()
    {
        require_once("{$_SERVER['DOCUMENT_ROOT']}/pg_module/inicis/INIStdweb/libs/INIStdPayUtil.php");

        if (empty($this->post['gou_number']))
            $this->processError('[gou_number] is empty', 'getSignatureData');
        if (empty($this->cart_data['price']))
            $this->getCartData();
        if (empty($this->keys['signKey']))
            $this->getMidAndSignKey();
        if ($this->error['boolean'])
            return $this->error;

        $SignatureUtil = new INIStdPayUtil();
        $this->keys['timestamp'] = $SignatureUtil->getTimestamp();
        $args = array(
            "oid" => $this->post['gou_number'],
            "price" => $this->cart_data['price'],
            "timestamp" => $this->key['timestamp']
        );
        # 가맹점 확인을 위한 signKey를 해시값으로 변경 (SHA-256방식 사용)
        $this->keys['sign'] = $SignatureUtil->makeSignature($args, "sha256");
        $this->keys['mKey'] = $SignatureUtil->makeHash($this->keys['signKey'],'sha256');

        return array(
            'sign' => $this->keys['sign']
            ,'mKey'=>$this->keys['mKey']
            ,'timestamp'=>$this->keys['timestamp']
        );
    }

    public function processError($msg=null, $method=null)
    {
        if (empty($msg)) return $this->error;
        if (!$this->error['boolean']) $this->error['boolean'] = true;
        $this->error['msg'][] = ($method) ? "{$method} : {$msg}" : $msg;
        return $this->error;
    }

    static function getMethod($pay_type)
    {
        switch ($pay_type) {
            case "card" :
                $method = "Card";
                break;
            case "bank_soodong" :
            case "escrow_vbank" :
                $method = "VBank";
                break;
        }
        return $method;
    }
}