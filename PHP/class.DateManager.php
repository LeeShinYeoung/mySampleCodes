<?php
/**
 * 기준일 설정(미설정시 현재날짜)
 * 
 * 목표일을 입력하면 기준일과의 영업일 차이를 반환
 * public getWorkDayDiff($targetDate)
 * 
 * 기준일로부터 $diff 이후의 영업일 계산 (배송일/정산일등 계산)
 * public getWorkDate($diff)  #목표일 계산
 * 
 * 기준일로부터 $diff 이전의 영업일 계산 ($diff 이전 시점부터 어제까지 구매결정된 건 계산)
 * public getPrevWorkDate($diff)
 * 
 * 2023년까지의 공휴일 반환
 * static getHolidayData()
 * 
 * 공휴일 데이터 Timestamp형식의 리스트로 변환
 * static getHolidayTimeList()
 * 
 * 공휴일인지 확인
 * static isHoliday($time)
 *
 * 주말인지 확인
 * static isWeekend($time)
 *
 * 영업일인지 확인
 * static isWorkday($time)
 *
 * 주문경로에 따른 배송처리 정보를 넣으면 정산예정일 관련 데이터를 반환합니다.
 * static getOrderScheduleMessage($param)
 *
 * 배송시작일 : 발송처리일
 * 배송완료일 : 제휴사로부터 가져온 배송완료일
 * 구매결정일 : 배송완료일로부터 제휴사에따른 자동구매확정 예상일 | 구매자 구매확정
 * 정산요청일 : 배송시작일로부터 영업일기준 5일이 경과 & 구매확정
 * 정산송금일 : 정산요청일로부터 영업일기준 익일
**/
class DateManager
{
    /**
     * 기준일 설정
     */
    public function __construct($basic=null)
    {
        if (is_null($basic)) $this->basicTime = $GLOBALS['TIME'];
        elseif (is_string($basic)) $this->basicTime = strtotime($basic);
        elseif (is_integer($basic)) $this->basicTime = $basic;

        $this->basicTime = strtotime(date('Y-m-d',$this->basicTime));
        $this->basicDate = date('Y-m-d',$this->basicTime);
    }
    /**
     * 날짜를 입력하면 기준일과의 영업일 차이를 반환
     */
    public function getWorkDayDiff($targetDate)
    {
        if (is_string($targetDate)) $targetTime = strtotime($targetDate);
        elseif (is_integer($targetDate)) $targetTime = $targetDate;
        else return false;

        $targetTime = strtotime(date('Y-m-d',$targetTime));

        if ($targetTime < $this->basicTime) {
            $big = $this->basicTime;
            $small = $targetTime;
        } elseif ($this->basicTime < $targetTime) {
            $big = $targetTime;
            $small = $this->basicTime;
        } else {
            return 0;
        }

        $firstWorkday = 0;
        for ($temp=$small; $temp<=$big; $temp+=86400) {
            if (self::isWorkday($temp)) {
                $firstWorkday = $temp;
                break;
            }
        }

        $cnt = 0;
        for ($temp=$firstWorkday+86400; $temp<=$big; $temp+=86400) {
            if (self::isWorkday($temp)) ++$cnt;
        }

        return $cnt;
    }
    /**
     * 기준일로부터 $diff 이후의 영업일을 구함
     * 배송일/정산일 등의 계산에 사용
     **/
    public function getWorkDate($diff)
    {
        $lastWorkday = $this->basicTime;
        for ($i=0; $i<100; ++$i) {
            if (self::isWorkday($lastWorkday)) break;
            $lastWorkday += 86400;
        }

        $cnt = 0;
        for ($i=0; $i<100; ++$i) {
            if ($cnt == $diff) break;
            $lastWorkday += 86400;
            if (self::isWorkday($lastWorkday)) ++$cnt;
        }

        return date('Y-m-d',$lastWorkday);
    }
    /**
     * 기준일로부터 $diff 이전의 영업일을 구함
     * $diff 영업일 이전 시점부터 어제까지 구매결정된 건들을 구할때 사용
     */
    public function getPrevWorkDate($diff) {

        if ($diff < 1) return $this->basicDate;

        $basicTime = $this->basicTime;
        for ($i=0; $i<100; ++$i) {
            if (self::isWorkday($basicTime)) break;
            $basicTime -= 86400;
        }

        $cnt = 0;
        for ($i=0; $i<100; ++$i) {
            $basicTime -= 86400;
            if (self::isWorkday($basicTime)) $cnt++;
            if ($cnt == $diff) break;
        }
        return date('Y-m-d',$basicTime);
    }
    static function getHolidayData() {
        static $data;
        if ($data) return $data;
        return $data = util::query_to_array("SELECT holiday_date, holiday_name FROM holiday");
    }
    static function getHolidayTimeList() {
        static $list;
        if ($list) return $list;
        $list = array_map('strtotime',util::array_column(self::getHolidayData(),'holiday_date'));
        return $list;
    }
    static function isWorkday($time) {
        if (self::isWeekend($time)) return false;
        elseif (self::isHoliday($time)) return false;
        return true;
    }
    static function isHoliday($time) {
        return in_array($time,self::getHolidayTimeList());
    }
    static function isWeekend($time) {
        return in_array(date('w',$time),array(0,6));
    }



    /**
     * 주문건 처리 정보를 넣으면 정산예정일 관련 데이터를 반환
     */
    static function getOrderScheduleMessage($param)
    {
        $return = array();
        
        $temp = array('배송시작일','배송완료일','구매결정일','정산요청일','정산송금일');
        for ($i=0,$end=count($temp); $i<$end; ++$i) {
            $param[$temp[$i]] = substr($param[$temp[$i]],0,10);
        }

        $param['주문경로'] = strtolower($param['주문경로']);

        if (empty($param['배송시작일'])) {
            if (empty($param['배송완료일'])) {
                $return[] = '배송시작일을 확인할 수 없습니다.';
                return $return;
            }
        } else {
            $return[] = "배송시작일 : {$param['배송시작일']}";
        }

        if (empty($param['배송완료일']) || $param['배송완료일'] == '0000-00-00') {
            if (!$param['구매결정일']) {
                $return[] = '배송완료일이 확인되지 않습니다.';
                $return[] = '- 배송완료일이 확인된 후 자동구매결정 예상일을 확인할 수 있습니다.';
                $return[] = '- 배송정보는 매일 새벽 중 자동으로 갱신됩니다.';
                $return[] = '- 송장번호 추적이 되지 않는 경우 정산이 지연될 수 있습니다.';
                return $return;
            }
        } else {
            $return[] = "배송완료일 : {$param['배송완료일']}";
        }

        if (empty($param['구매결정일'])) {
            $decision_due_date = 0;
            switch( $param['주문경로'] ) {
                case 'interpark' :
                    $dm = new DateManager($param['배송완료일']);
                    $dm->basicTime += (86400*9);
                    if (DateManager::isWorkday($dm->basicTime)) {
                        $decision_due_date = $dm->getWorkDate(2);
                    } else {
                        $decision_due_date = $dm->getWorkDate(1);
                    }
                    break;
                case 'auction' :
                case 'gmarket' :
                case '11st' :
                    $dm = new DateManager($param['배송완료일']);
                    $dm->basicTime += (86400*8);
                    if (DateManager::isWorkday($dm->basicTime)) {
                        $decision_due_date = $dm->getWorkDate(1);
                    } else {
                        $decision_due_date = $dm->getWorkDate(0);
                    }
                    break;
                default :
                    $dm = new DateManager($param['배송시작일']);
                    $decision_due_date = $dm->getWorkDate(6);
                    break;
            }
            $return[] = "자동구매결정일 : {$decision_due_date}(예정)";
            $return[] = "- 자동구매결정일은 예상일이며, 제휴사 사정에 따라 달라질 수 있습니다.";
        } else {
            $return[] = "구매결정일 : {$param['구매결정일']}";
        }

        if (empty($param['정산요청일'])) {
            if (empty($param['정산송금일'])) {
                $decision_date = ($param['구매결정일']) ? $param['구매결정일'] : $decision_due_date;
                $decision_time = strtotime($decision_date);

                $settlementDelay = ($param['파워셀러']) ? 2 : 5;
                $dm = new DateManager($param['배송시작일']);
                $minSettlementDate = $dm->getWorkDate($settlementDelay);
                $minSettlementTime = strtotime($minSettlementDate);

                $expectedSettlementRequestTime = ($decision_time < $minSettlementTime) ? $minSettlementTime : $decision_time;
                $expectedSettlementRequestTime = date('Y-m-d', $expectedSettlementRequestTime);
                $dm = new DateManager($expectedSettlementRequestTime);
                $expectedSettlementRequestTime = $dm->getWorkDate(0);

                $return[] = "정산요청일 : {$expectedSettlementRequestTime}(예정)";
            } else {
                $dm = new DateManager($param['정산송금일']);
                $settlementRequestCompleteDate = $dm->getPrevWorkDate(1);
                $return[] = "정산요청일 : {$settlementRequestCompleteDate}";
            }
        } else {
            $return[] = "정산요청일 : {$param['정산요청일']}";
        }

        if (empty($param['정산송금일'])) {
            $settlementRequestDate = $param['정산요청일'] ? $param['정산요청일'] : $expectedSettlementRequestTime;
            $dm = new DateManager($settlementRequestDate);
            $settlementCompleteDate = $dm->getWorkDate(1);

            $return[] = "정산송금일 : {$settlementCompleteDate}(예정)";
        } else {
            $return[] = "정산송금일 : {$param['정산송금일']}";
        }
        return $return;
    }
}
