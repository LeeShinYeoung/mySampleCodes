# Python sample codes
## ControlWebsite.py
자동화 작업을 위해 개발한 웹컨트롤 클래스 (selenium을 사용)

아래와 같이 간편하게 웹을 컨트롤 할수 있습니다.

### 실행
```python
# 인스턴스 생성
inst = ControlWebsite()
```
#### base_controller()를 사용할 경우
```python
inst.base_controller('goto_page', 'https://test.com/loginPage')
inst.base_controller('send_key', {'아이디': '//*[@id="id"]'})
inst.base_controller('send_key', {'비밀번호`': '//*[@id="password"]'})
inst.base_controller('click', '//*[@id="wrapper"]/div/form/div[2]/input')
```
#### exec_row()를 사용할 경우
```python
inst.exec_row({'goto_page': 'https://test.com/loginPage'})
inst.exec_row({'send_key': {'아이디': '//*[@id="id"]'}})
inst.exec_row({'send_key': {'비밀번호`': '//*[@id="password"]'}})
inst.exec_row({'click': '//*[@id="wrapper"]/div/form/div[2]/input'})
```
#### exec_flow_list()를 사용할 경우
```python
flow = [
    {'goto_page': 'https://test.com/loginPage'}
    , {'send_key': {'아이디': '//*[@id="id"]'}}
    , {'send_key': {'비밀번호`': '//*[@id="password"]'}}
    , {'click': '//*[@id="wrapper"]/div/form/div[2]/input'}
]
inst.exec_flow_list(flow)
```

#### Parameter
| event_type   | param                                 | explain              |
| ------------ | ------------------------------------- | -------------------- |
| goto_page    | string : Url                          | 페이지 접속          |
| click        | string : Xpath                        | 클릭                 |
| select       | dict : {Option name(str): Xpath(str)} | Selectbox 선택       |
| send_key     | dict : {Text(str): Xpath(str)}        | 태그에 값 입력       |
| alert        | boolean : True \| False               | Alert 확인 혹은 취소 |
| switch_frame | string : Xpath                        | Iframe 포커스 이동   |
| clear_text   | string : Xpath                        | 선택태그 값 초기화   |
| sleep        | int : second                          | 실행 딜레이          |





## ControlExcel.py

자동화 작업을 위해 간편하게 엑셀파일 데이터를 반환, 저장하고자 개발한 엑셀 컨트롤 클래스입니다 (.xls, .xlsx 가능)

### 실행
```python
# 인스턴스 생성
# 엑셀파일 경로 지정
path = 'C:/Users/hbmun/Desktop/Test_ControlExcel/Data.xls'
inst = ControlExcel(path, print_process=True)
```
#### 셀 반환
```python
inst.exec_get_cell(행, 열)
```
#### 행렬 반환
```python
# 시트 선택 (미사용시 첫번째 시트로 고정)
inst.set_sheet(시트이름 혹은 Index번호)

# 열 선택 (미사용시 모든 행렬 반환)
inst.set_column(['COL 1', 'COL 3', 'COL 5'])

# 실행
inst.exec_get_matrix()
```
#### 저장
```python
# 행렬 삽입
data = [
        ['COL 1', 'COL 2', 'COL 3']
        , ['a1', 'b1', 'c1']
        , ['a2', 'b2', 'c2']
        , ['a3', 'b3', 'c3']
        , ['a4', 'b4', 'c4']]
inst.exec_append_matrix(data)

# 인스턴스 생성시 지정한 경로에 paths에 있는 데이터 일괄 삽입
paths = [
         ['C:/Users/hbmun/Desktop/Test_ControlExcel/test_file_1.xls'
         , ['C:/Users/hbmun/Desktop/Test_ControlExcel/test_file_2.xls']
         , ['C:/Users/hbmun/Desktop/Test_ControlExcel/test_file_3.xls']
         , ['C:/Users/hbmun/Desktop/Test_ControlExcel/test_file_4.xls']]
inst.exec_append_paths(paths)

# 파일생성 (path가 없다면 인스턴스 생성시 지정한 경로에 파일 생성)
inst.exec_make_file(path)
```
#### 기타
```python
# Data.xls 를 포함하여
# Data (1).xls, Data (2).xls, Data (3).xls.... 파일이 있다면 리스트로 반환
dir_path = 'C:/Users/hbmun/Desktop/Test_ControlExcel/'
file_name = 'Data'
file_format = '.xls'
Control.get_same_file_paths(dir_path, file_name, file_format)
```
