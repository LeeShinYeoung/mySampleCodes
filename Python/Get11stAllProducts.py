import xlrd
import xlwt
import math
from Crawling.ControlBrowser import ControlBrowser
from Database.sbridge import DB_sbridge
from Database.localhost import DB_local
from pathlib import Path
from xlutils.copy import copy

# 1. DownloadProducts -> process
# 11번가 판매자 페이지에 직접 접속하여 판매중인 모든 상품을 엑셀로 다운받는다 (관리코드로 검색)
#
# 2. MergeProduct -> process
# 다운로드 받은 파일을 한 디렉토리에 넣어두어 실행시키면
# 그 디렉토리안 엑셀파일을 모두 합친다
#
# 3. MergeProduct -> insertDataBase
# 합쳐진 엑셀파일안 내용을 로컬 데이터베이스에 입력한다

class DownloadProducts(ControlBrowser):
    limit = 1000
    def getData(self, page):
        page = page * self.limit
        sql = "SELECT number FROM 상품테이블 WHERE product_stats = 0 ORDER BY number ASC LIMIT "+str(page)+", "+str(self.limit)
        data = DB_sbridge.queryToArray(sql)
        print('삽입데이터 : '+sql)
        return data

    def getCount(self):
        sql = "SELECT count(*) AS count FROM 상품테이블 WHERE product_stats = 0"
        count = DB_sbridge.queryToObject(sql)
        return count['count']

    def process(self):
        self.loginAndGoPage()
        count = self.getCount()
        count = math.ceil(count / self.limit) # 총갯수/횟수제한 => 33454 / 1000 = 34
        print('총 실행할 횟수 : '+str(count))
        for i in range(count):
            products = [str(v.get('number')) for v in self.getData(i)]
            confirm_count = len(products)
            products = "\n".join(products)
            self.controlingPage(products)
            print(str(i), '번째 다운로드완료', confirm_count)
        print('모든상품 다운로드완료')

    def loginAndGoPage(self):
        flow_list = []
        flow_list.append({'goto_page':'https://login.soffice.11st.co.kr/login/Login.page?returnURL=http%3A%2F%2Fsoffice.11st.co.kr%2FIndex.tmall'}) # 11번가 로그인
        flow_list.append({'send_key':{'판매자아이디':'//*[@id="loginName"]'}}) # 아이디 입력
        flow_list.append({'send_key':{'판매자비밀번호`':'//*[@id="passWord"]'}}) # 비밀번호 입력
        flow_list.append({'click':'//*[@id="layBody"]/div/div/form/div[2]/div/div/fieldset/div/div/input'}) # 로그인버튼 클릭
        flow_list.append({'goto_page':'http://soffice.11st.co.kr/product/SellProductAction.tmall?method=getSellProductList'}) # 상품조회 페이지
        for i, row in enumerate(flow_list):
            self.startFlow(row)

    def controlingPage(self, product_list):
        flow_list = []
        flow_list.append({'click':'//*[@id="table1"]/div[1]/table/tbody[1]/tr[2]/th[2]/label[2]'})
        flow_list.append({'send_key':{product_list:'//*[@id="prdNo"]'}})
        flow_list.append({'click':'//*[@id="ext-gen1019"]/div[2]/div[1]/div[3]/div[2]/a[3]'})
        flow_list.append({'clear':'//*[@id="prdNo"]'})
        flow_list.append({'sleep':5})
        for i, row in enumerate(flow_list):
            self.startFlow(row)


class MergeProduct():
    dir_path = 'C:/Users/hbmun/Desktop/st11_product/'
    file_name = 'list'
    format = '.xls'
    save_path = 'C:/Users/hbmun/Desktop/data.xls'

    def process(self):
        list = self.getFilePaths()
        for path in list:
            data = self.readXls(path)
            self.saveXls(data)
            print(path, '완료')

    def getFilePaths(self):
        i = 0
        paths = []
        while True:
            f = self.file_name
            f = f+' ('+str(i)+')' if i != 0 else f
            path = self.dir_path + f + self.format
            check = Path(path)
            if check.is_file() == False:
                break
            paths.append(path)
            i = i + 1
        return paths

    def readXls(self, path):
        wb = xlrd.open_workbook(path)
        ws = wb.sheet_by_index(0)  # 시트1 고정
        count = ws.nrows  # 행갯수

        data = []
        for i in range(count):
            if i == 0:
                continue
            row = {}
            row['title'] = ws.col_values(4)[i]  # 상품명
            row['product_number'] = ws.col_values(3)[i]  # 판매자상품코드
            row['price'] = ws.col_values(9)[i]  # 가격
            row['stats'] = ws.col_values(8)[i]  # 판매상태
            row['coop_number'] = ws.col_values(2)[i]  # 상품코드
            data.append(row)
        return data

    def saveXls(self, data):
        check = Path(self.save_path)
        if check.is_file() == False:  # 파일이 존재하지 않으면 생성후 재귀
            wb = xlwt.Workbook(encoding='utf-8')
            wb.add_sheet('sheet1')
            wb.save(self.save_path)
            self.saveXls(data)
            return False

        rb = xlrd.open_workbook(self.save_path)
        rs = rb.sheet_by_index(0)
        start = rs.nrows

        wb = copy(rb)
        ws = wb.get_sheet(0)
        for i, row in enumerate(data):
            for j, value in enumerate(row.items()):
                ws.write(start + i, j, value[1])
        wb.save(self.save_path)

    @classmethod
    def insertDataBase(cls):
        wb = xlrd.open_workbook(cls.save_path)
        ws = wb.sheet_by_index(0)  # 시트1 고정
        count = ws.nrows  # 행갯수

        for i in range(count):
            row = {}
            row['title'] = ws.col_values(0)[i]  # 상품명
            row['product_number'] = ws.col_values(1)[i]  # 판매자상품코드
            row['price'] = ws.col_values(2)[i]  # 가격
            row['stats'] = ws.col_values(3)[i]  # 판매상태
            row['coop_number'] = ws.col_values(4)[i]  # 상품코드
            DB_local.insertArray('_11st_products', row)
            print('insert ' + str(i))
        pass


if __name__ == '__main__':
    #(1)
    #inst = DownloadProducts()
    #inst.process()

    #(2)
    #inst = MergeProduct()
    #inst.process()

    #(3)
    #MergeProduct.insertDataBase()
