import math
from Tool.ControlWebsite import ControlWebsite
from Tool.ControlExcel import ControlExcel
from Database.sbridge import DB_sbridge
from Database.localhost import DB_local


# 1) 11번가 판매자 페이지에 접속해서 판매중인 상품을 엑셀파일로 모두 다운받는다
# exec_download_products()

# 2) 다운받은 엑셀 파일들을 모두 합친다
# exec_combine_all_excel(path_info)

# 3) 모두 합쳐진 엑셀파일에 있는 데이터를 로컬 데이터베이스에 입력한다
# exec_insert_database(save_path)

class Get11stAllProducts:
    def __init__(self):
        self.limit = 1000
        self.web_controller = None

    # 11번가 판매중 상품 엑셀로 다운로드
    def exec_download_products(self):
        self.login_and_go_page()
        count = self.get_count()
        count = math.ceil(count / self.limit)  # 총갯수/횟수제한 => 33454 / 1000 = 34
        print('총 실행할 횟수 : ' + str(count))
        for i in range(count):
            products = [str(v.get('number')) for v in self.get_data(i)]
            confirm_count = len(products)
            products = "\n".join(products)
            self.controlling_page(products)
            print(str(i), '번째 다운로드완료', confirm_count)
        print('모든상품 다운로드완료')

    # LIMIT 만큼 상품테이블 상품번호 반환
    def get_data(self, page):
        page = page * self.limit
        sql = "SELECT number FROM 상품테이블 WHERE product_stats = 0 ORDER BY number ASC LIMIT " + str(
            page) + ", " + str(self.limit)
        product_numbers = DB_sbridge.queryToArray(sql)
        print('삽입데이터 : ' + sql)
        return product_numbers

    # 판매자 페이지 로그인해서 접속후 상품조회 페이지로 이동
    def login_and_go_page(self):
        # 1) 판매자 페이지 접속
        # 2) 아이디 입력
        # 3) 비밀번호 입력
        # 4) 로그인버튼 클릭
        # 5) 상품조회 페이지로 이동
        flow = [
            {'goto_page': 'https://login.soffice.11st.co.kr/login/Login.page?returnURL=http%3A%2F%2Fsoffice.11st.co.kr%2FIndex.tmall'}
            , {'send_key': {'판매자ID': '//*[@id="loginName"]'}}
            , {'send_key': {'판매자PW`': '//*[@id="passWord"]'}}
            , {'click': '//*[@id="layBody"]/div/div/form/div[2]/div/div/fieldset/div/div/input'}
            , {'goto_page': 'http://soffice.11st.co.kr/product/SellProductAction.tmall?method=getSellProductList'}
        ]
        self.web_controller = ControlWebsite()
        self.web_controller.exec_flow_list(flow)

    # 판매자 상품코드로 검색후 엑셀다운로드
    def controlling_page(self, product_list):
        # 1) 판매자 상품번호 선택
        # 2) 검색 박스에 가져온 auction_product number(판매자 상품번호) 1000개 삽입
        # 3) 엑셀 다운로드 버튼 클릭
        # 4) 검색 박스에 입력한 auction_product number(판매자 상품번호) 모두 삭제
        # 5) 5초 딜레이
        flow = [
            {'click': '//*[@id="table1"]/div[1]/table/tbody[1]/tr[2]/th[2]/label[2]'}
            , {'send_key': {product_list: '//*[@id="prdNo"]'}}
            , {'click': '//*[@id="ext-gen1019"]/div[2]/div[1]/div[3]/div[2]/a[3]'}
            , {'clear': '//*[@id="prdNo"]'}
            , {'sleep': 5}
        ]
        self.web_controller.exec_flow_list(flow)

    # 엑셀 합치기
    @classmethod
    def exec_combine_all_excel(cls, path_info):
        excel_ctrl = ControlExcel(path_info['save_file_path'], print_process=True)
        paths = excel_ctrl.get_same_file_paths(path_info['dir_path'], path_info['file_name'], path_info['file_format'])
        excel_ctrl.exec_append_paths(paths)

    # 로컬DB에 Insert
    @classmethod
    def exec_insert_database(cls, save_path):
        excel_ctrl = ControlExcel(save_path, print_process=True)
        excel_ctrl.set_columns([
            '상품번호'
            , '판매자상품코드'
            , '상품명'
            , '판매상태'
            , '판매가'
        ])
        excel_data = excel_ctrl.exec_get_matrix()
        for i, row in enumerate(excel_data):
            DB_local.insertArray('_11st_products', row)
            if i % 50 is 0:
                print('insert {}'.format(str(i)))

    # 판매중인 상품갯수 반환
    @classmethod
    def get_count(cls):
        sql = "SELECT count(*) AS count FROM 상품테이블 WHERE product_stats = 0"
        count = DB_sbridge.queryToObject(sql)
        return count['count']


if __name__ == '__main__':
    # 인스턴스 생성
    instance = Get11stAllProducts()

    # 11번가 판매자 페이지에 접속해서 판매중인 상품을 엑셀파일로 모두 다운받는다
    # instance.exec_download_products()

    data = {
         'dir_path': 'C:/Users/hbmun/Desktop/st11_product/'
         , 'file_name': 'list'
         , 'file_format': '.xls'
         , 'save_file_path': 'C:/Users/hbmun/Desktop/st11_product/save.xlsx'
    }
    # 다운받은 엑셀 파일들을 모두 합친다
    # instance.exec_combine_all_excel(data)

    # 모두 합쳐진 엑셀파일에 있는 데이터를 로컬 데이터베이스에 입력한다
    # instance.exec_insert_database(data['save_file_path'])
