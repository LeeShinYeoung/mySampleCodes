<?
include_once("{$_SERVER['DOCUMENT_ROOT']}/coop/11st/class.st11.php");
/**
    등록된 주소지조회
    static getRegisterdAddress($type) 
    
    Request용 주소데이터 추출
    static makeRequestAddress($data)
    
    도로명, 지번주소 검색
    static searchAddress($serach_kwd)
    
    도로명주소 검색
    static searchAddressByRoadName($road_addr)
    
    지번주소 검색
    static searchAddressByJibun($jibun_addr)
    
    등록/수정 XML생성
    static makeAddressXml($user_data, $seq = null)
    
    동기화
    static syncSellerAddressManual($user_data,$address_type)
    
    동기화 후처리 (로그, 업데이트)
    static updateCoopSellerAddress($data,$mode)
    
    크론탭 전용
    public crontab_needSync()
 */
class St11Address extends st11
{
    /**
     등록된 주소지 조회
     **/
    static function getRegisterdAddress($type)
    {
        $url = ($type == 'out_address') ? 'http://api.11st.co.kr/rest/areaservice/outboundarea' : 'http://api.11st.co.kr/rest/areaservice/inboundarea';
        $method = 'GET';
        $xml = array();
        $response = parent::request($url,$method,$xml);
        $response_array = parent::xml_to_array($response);
        return $response_array['inOutAddress'];
    }
    
    /**
    Request용 주소데이터 추출
    **/
    static function makeRequestAddress($data)
    {
        $length = strlen(str_replace('-','',$data['post_no']));
        
        if (empty($data['addr_1']) && empty($data['addr_2'])) return false;
        $address = (empty($data['addr_2'])) ? $data['addr_1'] : $data['addr_2'];
        $address = explode(' ',$address);
        $return = array();

        for ($i=0; $i<count($address); $i++) {
            $u = $address[$i];
            if ($i == 0) {
                $return['do'] = $u;
                continue;
            }
            if (preg_match('/시$|군$|구$/i',$u)) $return['si'][] = $u;
            else $return['etc'][] = $u;
        }
        $return['si'] = implode(' ',$return['si']);
        $return['etc'] = implode(' ',$return['etc']);

        switch ($length) {
            case 5:
                if (preg_match("/[^()]+/",$return['etc'],$match)) {
                    list($match) = $match;
                    $return['etc'] = $match;
                }
            break;
            default:
            case 6: 
                $etc = '';
                if (preg_match("/(^.+면|^.+동|^.+읍)[\s]/", $return['etc'], $match)) {
                    $etc .= $match[count($match)-1].' ';
                }
                if (preg_match_all("/[^()]+\((.+읍|.+면|.+동)/", $return['etc'], $match)) {
                    list($match) = $match[count($match[0])];
                    $etc .= $match;
                }
                $return['etc'] = ($etc) ? $etc : $return['etc'];
            break;
        }
        return $return;
    }
    
    /**
     * 18.11.23   추가
     * 11번가에 도로명과 지번모두 검색할수 있는 API가 추가됨
     */
    static function searchAddress($search_kwd)
    {
        $url ="http://api.11st.co.kr/rest/commonservices/v2/searchAddr";
        $method = "POST";
        $xml_data = array();
        $xml_data['searchRoadAddrKwd'] = $search_kwd['search_addr_kwd'];
        $xml_data['fetchSize'] = $search_kwd['fetch_size'];
        $xml_data['pageNum'] = $search_kwd['page_num'];
        $xml = "<RoadAddrSearchRequest>".parent::array_to_xml($xml_data)."</RoadAddrSearchRequest>";
        $response = parent::request($url,$method,$xml);
        $response_array = parent::xml_to_array($response);

        return $response_array;
    }
    
    /**
     * 도로명주소로 조회
     * **/
    static function searchAddressByRoadName($road_addr)
    {
        $url = 'http://api.11st.co.kr/rest/commonservices/roadAddr';
        $method = 'POST';
        $xml_data = array();
        $xml_data['searchRoadAddrKwd'] = $road_addr['search_addr_kwd'];
        $xml_data['sido'] = $road_addr['sido'];
        $xml_data['sigungu'] = $road_addr['sigungu'];
        $xml_data['fetchSize'] = $road_addr['fetch_size'];
        $xml = '<RoadAddrSearchRequest>'.parent::array_to_xml($xml_data).'</RoadAddrSearchRequest>';
        $response = parent::request($url,$method,$xml);
        $response_array = parent::xml_to_array($response);
        return $response_array;
    }
    
    /**
     * 지번주소 조회
     */
    static function searchAddressByJibun($jibun_addr)
    {
        if (is_array($jibun_addr)) $jibun_addr = $jibun_addr['sido'].' '.$jibun_addr['sigungu'].' '.$jibun_addr['search_addr_kwd'];
        $addr_encoded = urlencode(iconv('euckr','utf8',$jibun_addr));
        $url = "http://api.11st.co.kr/rest/commonservices/zipCode/{$addr_encoded}";
        $method = "GET";
        $response = parent::request($url,$method);
        $response_array = parent::xml_to_array($response);
        return $response_array;
    }

    /**
     * 반품주소지를 등록/수정하는 xml생성
     */
    static function makeAddressXml($user_data, $seq = null)
    {
        $xml_data = array();
        if ($seq) $xml_data['addrSeq'] = $seq;
        $xml_data['addrNm'] = $user_data['user_id']; # 주소명(아이디)
        $xml_data['rcvrNm'] = $user_data['user_name']; # 이름
        $xml_data['gnrlTlphnNo'] = standardizePhone($user_data['phone']); # 일반전화
        $xml_data['prtblTlphnNo'] = standardizePhone($user_data['hphone']); # 휴대전화
        if ($user_data['post_no']) $xml_data['mailNO'] = $user_data['post_no']; # 우편번호 (선택)
        if ($user_data['post_no_order']) $xml_data['mailNOSeq'] = $user_data['post_no_order']; # 우편번호 순번 (선택)
        if ($user_data['building_no']) $xml_data['buildMngNO'] = $user_data['building_no']; # 건물관리번호 (선택)
        if ($user_data['addr_ord']) $xml_data['lnmAddrSeq'] = $user_data['addr_ord']; # 지번순번 (선택)
        $xml_data['dtlsAddr'] = $user_data['detail_addr']; # 상세주소
        $xml = '<InOutAddress>'.parent::array_to_xml($xml_data).'</InOutAddress>';
        return $xml;
    }

    /**
     * 반품주소지를 수동 등록/수정
     */
    static function syncSellerAddressManual($user_data,$address_type)
    {
        switch ($address_type)
        {
            case 'return_address': #반품교환지
                $add_url = 'http://api.11st.co.kr/rest/areaservice/registerRtnAddress';
                $mod_url = 'http://api.11st.co.kr/rest/areaservice/updateRtnAddress';
                break;
            case 'out_address': #출고지
                $add_url = 'http://api.11st.co.kr/rest/areaservice/registerOutAddress';
                $mod_url = 'http://api.11st.co.kr/rest/areaservice/updateOutAddress';
                break;
            default:
                return array('result'=>'등록되지 않은 address_type입니다.','result_bool'=>false);
                break;
        }
        $seq = $user_data['coop_value'];
        
        $url = (empty($seq)) ? $add_url : $mod_url;
        $mode = (empty($seq)) ? 'add' : 'mod';
        $method = 'POST';

        $xml_data = self::makeAddressXml($user_data,$seq);
        $response = parent::request($url,$method,$xml_data);
        $response_array = parent::xml_to_array($response);

        $return = array();
        $return['coop_name'] = '11st.'.$address_type;
        $return['reg_date'] = $GLOBALS['YMDHIS'];
        $return['user_id'] = $user_data['user_id'];

        $return['result'] = (isset($response_array['inOutAddress'])) ? $response_array['inOutAddress']['addrSeq'] : $response_array['result_message'];
        $return['result_bool'] = (isset($response_array['inOutAddress'])) ? true : false;

        self::updateCoopSellerAddress($return,$mode);
        return $return;
    }
    
    /**
     * 동기화후 반환된 데이터를 테이블에 넣는다
     */
    static function updateCoopSellerAddress($data,$mode)
    {
        $sql = "SELECT      *
                FROM        반품주소지관련테이블
                WHERE       user_id = '{$data['user_id']}'
                AND         coop_name = '{$data['coop_name']}'
        ";
        list($CSA_data) = util::query_to_array($sql);

        if ($CSA_data) {
            if ($mode == 'add')
                $status = ($data['result_bool']) ? '정상' : '신규동기화실패';
            elseif ($mode == 'mod')
                $status = ($data['result_bool']) ? '정상' : '수정동기화실패';

            $update_coop_value = ($data['result_bool']) ? ", coop_value = '".$data['result']."'" : '';
            $sql = "
                UPDATE 반품주소지관련테이블
                SET    sync_date = '{$data['reg_date']}'
                ,      status = '{$status}'
                {$update_coop_value}
                WHERE  number = '{$CSA_data['number']}'
                ";
            query($sql);
        } else {
            $CSA = array();
            $CSA['user_id'] = $data['user_id'];
            $CSA['coop_name'] = $data['coop_name'];
            $CSA['coop_value'] = ($data['result_bool']) ? $data['result'] : '';
            $CSA['status'] = ($data['result_bool']) ? '정상' : '신규동기화실패';
            $CSA['reg_date'] = $data['reg_date'];
            util::insert_array('반품주소지관련테이블',$CSA);
        }
    }
    

    public function crontab_needSync($type)
    {
        if (!preg_match('/out_address|return_address/',$type)) return false;

        $sql = "
            SELECT  CSA.user_id
            FROM    반품주소지관련테이블 AS CSA
            WHERE   CSA.coop_name LIKE ('11st.{$type}')
        ";
        $coop_id_list = util::query_to_array($sql);
        $coop_id_list = util::array_column($coop_id_list,'user_id');
        $coop_id_list = "'".implode("','",$coop_id_list)."'";

        $sql = "
            SELECT   HM.user_id
            FROM 상품테이블 AS AP
            LEFT OUTER JOIN 회원테이블 AS HM ON HM.user_id = AP.id
            WHERE HM.user_id IS NOT NULL
            AND AP.product_stats = '0'
            AND HM.user_id NOT IN ({$coop_id_list})
            GROUP BY HM.user_id
        ";
        $id_list = util::query_to_array($sql);
        $id_list = util::array_column($id_list,'user_id');
        if ($GLOBALS['print_process']) echoDev("아래 쿼리후 남은 회원수 : ".count($id_list),$sql);

        $CSA = array();
        for ($i=0; $i<count($id_list); $i++)
        {
            $row = $id_list[$i];
            $CSA_row = array();
            $CSA_row['user_id'] = $row;
            $CSA_row['coop_name'] = '11st.'.$type;
            $CSA_row['status'] = '동기화요청필요';
            $CSA_row['reg_date'] = $GLOBALS['YMDHIS'];
            $CSA[] = $CSA_row;
        }
        util::insert_multi_array('반품주소지관련테이블',$CSA);
    }
}