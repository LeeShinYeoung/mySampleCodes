from selenium import webdriver
from selenium.webdriver.support.ui import Select
from selenium.webdriver.common.alert import Alert
import time, datetime

class ControlBrowser:
    def __init__(self):
        self.driver = webdriver.Chrome('C://python/webdriver/chromedriver.exe')
        self.driver.implicitly_wait(5)
        pass
    def startFlow(self, row):
        for type, data in row.items():
            if type == 'goto_page':
                self.driver.get(data)
            elif type == 'send_key':
                for value, xpath in data.items():
                    self.driver.find_element_by_xpath(xpath).send_keys(value)
            elif type == 'click':
                self.driver.find_element_by_xpath(data).click()
            elif type == 'select':
                for value, xpath in data.items():
                    select = Select(self.driver.find_element_by_xpath(xpath))
                    select.select_by_visible_text(value)
            elif type == 'alert':
                if data:
                    Alert(self.driver).accept()
                else:
                    Alert(self.driver).dismiss()
            elif type == 'sleep':
                time.sleep(int(data))
            elif type == 'switch_frame':
                self.driver.switch_to.frame(self.driver.find_element_by_xpath(data))
            elif type == 'clear':
                self.driver.find_element_by_xpath(data).clear()
            elif type == 'switch_window':
                window = self.driver.get_window_rect()
                self.driver.switch_to().window(window)
            self.driver.implicitly_wait(5)

