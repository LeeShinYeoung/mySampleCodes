import os.path
import time
from selenium import webdriver
from selenium.webdriver.support.ui import Select
from selenium.webdriver.common.alert import Alert


# ControlWebsite
# 웹 컨트롤을 위한 클래스
#
# exec_flow_list(Array)
# 배열 순차적으로 실행
#
# exec_row(Dictionary)
# Dictionary 자료형으로 한번 실행
#
# base_controller(event_type, param)
# event_type, param에 따라 한번 실행
#
# event_click(xpath)    클릭
# event_goto_page(url)    페이지 접속
# event_select(dict_data)    Selectbox 선택
# event_send_key(dict_data)    태그에 값입력
# event_alert(boolean)    alert창 확인 혹은 취소
# event_switch_frame(xpath)    iframe 포커스 이동
# event_clear_text (xpath)    선택태그 값 초기화
# event_sleep(sec)    실행 딜레이


class ControlWebsite:
    def __init__(self):
        driver_path = os.path.expanduser('~') + '\webdriver\chromedriver2.exe'
        self.driver = webdriver.Chrome(driver_path)
        self.driver.implicitly_wait(5)

    # 배열 순차적으로 실행
    def exec_flow_list(self, list, exec_finally=False):
        for row in list:
            for event_type, param in row.items():
                self.base_controller(event_type, param)
        if exec_finally:
            self.driver.close()

    # Dictionary 자료형으로 한번 실행
    def exec_row(self, row):
        for event_type, param in row.items():
            print(event_type)
            self.base_controller(event_type, param)

    # event_type, param에 따라 한번 실행
    def base_controller(self, event_type, param=None):
        try:
            event_method_name = "event_" + str(event_type)
            start_event = getattr(self, event_method_name)
            if param:
                start_event(param)
            else:
                start_event()
        except AttributeError as e:
            print(e, '\n존재하지 않는 동작입니다 : ' + str(event_type))
            self.driver.close()
        except Exception as e:
            print(e)
            self.driver.close()

    def event_click(self, xpath):
        if not isinstance(xpath, str):
            raise Exception('클릭할 버튼의 Xpath를 입력해주세요')
        self.driver.find_element_by_xpath(xpath).click()

    def event_goto_page(self, url):
        if not isinstance(url, str):
            raise Exception('접속하려는 사이트 url을 입력해주세요.')
        self.driver.get(url)

    def event_select(self, dict_data):
        if not isinstance(dict_data, dict):
            raise Exception('선택할 Option명과 SelectBox의 Xpath를 입력해주세요.')
        for value, xpath in dict_data.items():
            select = Select(self.driver.find_element_by_xpath(xpath))
            select.select_by_visible_text(value)

    def event_send_key(self, dict_data):
        if not isinstance(dict_data, dict):
            raise Exception('입력할 text와 선택하는 태그의 xpath를 입력해주세요.')
        for value, xpath in dict_data.items():
            self.driver.find_element_by_xpath(xpath).send_keys(value)

    def event_alert(self, boolean):
        if not isinstance(boolean, boolean):
            raise Exception('확인을 누루려면 True, 취소를 누루려면 False를 입력해주세요')
        if boolean:
            Alert(self.driver).accept()
        else:
            Alert(self.driver).dismiss()

    def event_switch_frame(self, xpath):
        if not isinstance(xpath, str):
            raise Exception('이동할 프레임의 Xpath를 입력해주세요')
        self.driver.switch_to.frame(self.driver.find_element_by_xpath(xpath))

    def event_clear_text(self, xpath):
        if not isinstance(xpath, str):
            raise Exception('텍스트를 초기화할 태그의 Xpath를 입력해주세요')
        self.driver.find_element_by_xpath(xpath).clear()

    def event_sleep(self, sec):
        if not isinstance(sec, int):
            raise Exception('딜레이할 숫자를 Int자료형으로 입력해주세요')
        time.sleep(int(sec))


if __name__ == '__main__':
    pass
